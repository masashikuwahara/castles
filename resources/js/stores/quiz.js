import { defineStore } from 'pinia'
export const useQuizStore = defineStore('quiz', {
  state: () => ({ seen: [], correct: 0, wrong: 0, answered: 0, total: 5,}),
  getters: {
    finished: (s) => s.answered >= s.total,
    progress: (s) => ({ done: s.answered, total: s.total }),
  },
  actions: {
    start(count = 5) {        // 新セッション開始
      this.seen = []
      this.correct = 0
      this.wrong = 0
      this.answered = 0
      this.total = Number(count) || 5
    },
    push(id){ if(!this.seen.includes(id)) this.seen.push(id) },
    mark(ok) {
      ok ? this.correct++ : this.wrong++
      this.answered++
    },
    reset(){ this.seen = []; this.correct = this.wrong = 0 }
  }
})
