import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
    }),
    getters: {
        count: (state) => state.items.length,
        totalPrice: (state) => state.items.reduce((sum, item) => sum + parseFloat(item.price), 0),
    },
    actions: {
        addToCart(course) {
            if (!this.items.find(item => item.id === course.id)) {
                this.items.push(course);
            }
        },
        removeFromCart(courseId) {
            this.items = this.items.filter(item => item.id !== courseId);
        }
    }
});