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

const localColumns = reactive(JSON.parse(JSON.stringify(props.columns)))

const labels = {
  created: 'Created',
  accepted: 'Accepted',
  in_progress: 'In progress',
  in_review: 'In review',
  done: 'Done',
}

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
            <article class="rounded border bg-white p-3 shadow-sm" :data-task-id="task.id">
              <div class="font-medium">{{ task.title }}</div>
              <div class="text-xs text-gray-500 mt-1">
                #{{ task.id }} · sort {{ task.sort_order }} · status {{ task.status }}
                <span v-if="role === 'viewer'" class="ml-2">
                  (assigned_to_me: {{ task.assigned_to_me ? 'yes' : 'no' }})
                </span>
              </div>
            </article>
          </template>
        </draggable>
      </section>
    </div>
  </div>
</template>