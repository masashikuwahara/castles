import { defineStore } from 'pinia'
export const useQuizStore = defineStore('quiz', {
  state: () => ({ seen: [], correct: 0, wrong: 0 }),
  actions: {
    push(id){ if(!this.seen.includes(id)) this.seen.push(id) },
    mark(ok){ ok ? this.correct++ : this.wrong++ },
    reset(){ this.seen = []; this.correct = this.wrong = 0 }
  }
})
