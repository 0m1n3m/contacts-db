<script setup>
import { reactive } from 'vue'
import draggable from 'vuedraggable'

const props = defineProps({
  columns: { type: Object, required: true },
  auth: { type: Object, required: true },
})

const user = props.auth?.user ?? null
const role = user?.role ?? 'viewer'
const myId = user?.id ?? null

const localColumns = reactive({})
const parsed = JSON.parse(JSON.stringify(props.columns))

Object.keys(parsed).forEach(status => {
  if (role === 'viewer') {
    localColumns[status] = parsed[status].filter(task => task.assigned_to_me)
  } else if (role === 'editor') {
    localColumns[status] = parsed[status].filter(task => 
      task.created_by === myId || task.assigned_to_me
    )
  } else {
    localColumns[status] = parsed[status]
  }
})

const labels = {
  created: 'Created',
  accepted: 'Accepted',
  in_progress: 'In progress',
  in_review: 'In review',
  done: 'Done',
}

const statusOrder = ['created', 'accepted', 'in_progress', 'in_review', 'done']

function reindex(list) {
  list.forEach((t, idx) => { t.sort_order = (idx + 1) * 10 })
}

function closestStatus(el) {
  const node = el?.closest?.('[data-status]')
  return node?.getAttribute?.('data-status') ?? null
}

function allowedTargetsForRole(fromStatus) {
  if (role === 'admin') return ['created', 'accepted', 'in_progress', 'in_review', 'done']

  if (role === 'editor') {
    switch (fromStatus) {
      case 'created': return ['accepted', 'in_progress']
      case 'in_progress': return ['in_review', 'done']
      case 'done': return ['in_review', 'in_progress']
      default: return []
    }
  }

  switch (fromStatus) {
    case 'created': return ['in_progress']
    case 'accepted': return ['in_progress']
    case 'in_progress': return ['in_review']
    default: return []
  }
}

function canMove(evt) {
  const task = evt.draggedContext?.element
  if (!task?.id) return false

  const fromStatus = closestStatus(evt.from)
  const toStatus = closestStatus(evt.to)
  if (!fromStatus || !toStatus) return false

  if (role === 'viewer' && !task.assigned_to_me) return false
  if (role === 'viewer' && fromStatus === toStatus) return false

  if (fromStatus === toStatus) return true
  return allowedTargetsForRole(fromStatus).includes(toStatus)
}

function getCursorClass(task) {
  const fromStatus = task.status
  const allowedTargets = allowedTargetsForRole(fromStatus)

  if (role === 'viewer' && !task.assigned_to_me) return 'cursor-not-allowed'
  if (role === 'editor' && task.created_by !== myId && !task.assigned_to_me) return 'cursor-not-allowed'
  if (allowedTargets.length === 0) return 'cursor-not-allowed'

  return 'cursor-grab hover:cursor-grabbing'
}

function canMoveTask(task) {
  const allowedTargets = allowedTargetsForRole(task.status)
  
  if (role === 'viewer' && !task.assigned_to_me) return false
  if (role === 'editor' && task.created_by !== myId && !task.assigned_to_me) return false
  if (allowedTargets.length === 0) return false
  
  return true
}

function canMoveUp(task) {
  const tasks = localColumns[task.status] ?? []
  const index = tasks.findIndex(t => t.id === task.id)
  return index > 0
}

function canMoveDown(task) {
  const tasks = localColumns[task.status] ?? []
  const index = tasks.findIndex(t => t.id === task.id)
  return index < tasks.length - 1
}

function canChangeStatus(task, direction) {
  const allowedTargets = allowedTargetsForRole(task.status)
  if (direction === 'up') {
    const currentIndex = statusOrder.indexOf(task.status)
    return currentIndex > 0 && allowedTargets.includes(statusOrder[currentIndex - 1])
  } else {
    const currentIndex = statusOrder.indexOf(task.status)
    return currentIndex < statusOrder.length - 1 && allowedTargets.includes(statusOrder[currentIndex + 1])
  }
}

async function moveTaskWithinStatus(task, direction) {
  const tasks = localColumns[task.status]
  const index = tasks.findIndex(t => t.id === task.id)
  
  if (direction === 'up' && index > 0) {
    [tasks[index], tasks[index - 1]] = [tasks[index - 1], tasks[index]]
  } else if (direction === 'down' && index < tasks.length - 1) {
    [tasks[index], tasks[index + 1]] = [tasks[index + 1], tasks[index]]
  }
  
  reindex(tasks)
  await persistMove(task.id, task.status, task.status)
}

async function changeTaskStatus(task, direction) {
  const currentIndex = statusOrder.indexOf(task.status)
  let newStatus
  
  if (direction === 'up' && currentIndex > 0) {
    newStatus = statusOrder[currentIndex - 1]
  } else if (direction === 'down' && currentIndex < statusOrder.length - 1) {
    newStatus = statusOrder[currentIndex + 1]
  }
  
  if (newStatus) {
    const fromStatus = task.status
    const toStatus = newStatus
    
    // Mover tarea de una columna a otra
    const fromTasks = localColumns[fromStatus]
    const toTasks = localColumns[toStatus]
    
    const index = fromTasks.findIndex(t => t.id === task.id)
    if (index !== -1) {
      const [moved] = fromTasks.splice(index, 1)
      moved.status = toStatus
      toTasks.push(moved)
      
      reindex(fromTasks)
      reindex(toTasks)
      
      await persistMove(task.id, fromStatus, toStatus)
    }
  }
}

async function persistMove(taskId, fromStatus, toStatus) {
  const from_ordered_task_ids = (localColumns[fromStatus] ?? []).map(t => t.id)
  const to_ordered_task_ids = (localColumns[toStatus] ?? []).map(t => t.id)

  await window.axios.patch(`/tasks/${taskId}/move`, {
    to_status: toStatus,
    from_ordered_task_ids,
    to_ordered_task_ids,
  })
}

async function onEnd(_status, evt) {
  const taskId = Number(evt?.item?.dataset?.taskId)
  const fromStatus = closestStatus(evt.from)
  const toStatus = closestStatus(evt.to)
  if (!taskId || !fromStatus || !toStatus) return

  try {
    reindex(localColumns[fromStatus] ?? [])
    reindex(localColumns[toStatus] ?? [])

    await persistMove(taskId, fromStatus, toStatus)

    if (fromStatus !== toStatus) {
      const moved = (localColumns[toStatus] ?? []).find(t => t.id === taskId)
      if (moved) moved.status = toStatus
    }
  } catch {
    window.location.reload()
  }
}
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Tasks Board</h1>
      <div class="text-sm text-gray-500">
        User: {{ myId ?? 'guest' }} · Rol: {{ role }}
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <section
        v-for="(tasks, status) in localColumns"
        :key="status"
        class="rounded border bg-gray-50 flex flex-col"
        :data-status="status"
      >
        <header class="px-3 py-2 border-b bg-white rounded-t">
          <div class="flex items-center justify-between">
            <h2 class="font-semibold">{{ labels[status] ?? status }}</h2>
            <span class="text-xs text-gray-500">{{ tasks.length }}</span>
          </div>
        </header>

        <draggable
          :list="localColumns[status]"
          item-key="id"
          group="tasks"
          class="p-3 space-y-2 min-h-[240px]"
          :move="canMove"
          @end="(evt) => onEnd(status, evt)"
        >
          <template #item="{ element: task }">
            <div
              class="rounded border bg-white p-3 shadow-sm"
              :data-task-id="task.id"
            >
              <a
                :href="`/tasks/${task.id}`"
                class="block font-medium hover:shadow-md transition mb-2"
                :class="getCursorClass(task)"
              >
                {{ task.title }}
              </a>

              <div class="text-xs text-gray-500 mb-2">
                status {{ task.status }}
                <span class="ml-2">
                  (assigned_to_me: {{ task.assigned_to_me ? 'yes' : 'no' }})
                </span>
              </div>

              <!-- Botones de control -->
              <div class="flex flex-wrap gap-1" v-if="canMoveTask(task)">
                <!-- Mover dentro del status -->
                <button
                  v-if="canMoveUp(task)"
                  @click="moveTaskWithinStatus(task, 'up')"
                  class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300 transition"
                  title="Move up"
                >
                  ↑
                </button>
                <button
                  v-if="canMoveDown(task)"
                  @click="moveTaskWithinStatus(task, 'down')"
                  class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300 transition"
                  title="Move down"
                >
                  ↓
                </button>

                <!-- Cambiar status -->
                <button
                  v-if="canChangeStatus(task, 'up')"
                  @click="changeTaskStatus(task, 'up')"
                  class="px-2 py-1 bg-blue-200 text-blue-700 text-xs rounded hover:bg-blue-300 transition"
                  title="Move to previous status"
                >
                  ←
                </button>
                <button
                  v-if="canChangeStatus(task, 'down')"
                  @click="changeTaskStatus(task, 'down')"
                  class="px-2 py-1 bg-green-200 text-green-700 text-xs rounded hover:bg-green-300 transition"
                  title="Move to next status"
                >
                  →
                </button>
              </div>
            </div>
          </template>
        </draggable>
      </section>
    </div>
  </div>
</template>