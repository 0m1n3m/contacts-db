import './bootstrap'
import '../css/app.css'

import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()

// Boot Inertia only when the page contains an Inertia mount element.
const inertiaRoot = document.querySelector('[data-page]')

if (inertiaRoot) {
  const { createApp, h } = await import('vue')
  const { createInertiaApp } = await import('@inertiajs/vue3')

  const pages = import.meta.glob('./Pages/**/*.vue')

  createInertiaApp({
    resolve: (name) => {
      const page = pages[`./Pages/${name}.vue`]
      if (!page) throw new Error(`Inertia page not found: ./Pages/${name}.vue`)
      return page()
    },
    setup({ el, App, props, plugin }) {
      createApp({ render: () => h(App, props) })
        .use(plugin)
        .mount(el)
    },
  })
}

// Vue island for Tasks Board (Blade page)
const boardEl = document.getElementById('tasks-board')
if (boardEl) {
  const { createApp } = await import('vue')
  const Board = (await import('./Pages/Tasks/Board.vue')).default

  const columns = JSON.parse(boardEl.dataset.columns || '{}')
  const auth = JSON.parse(boardEl.dataset.auth || '{}')

  createApp(Board, { columns, auth }).mount(boardEl)
}