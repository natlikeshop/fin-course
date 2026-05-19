// main.js - Core UI Interactions for Fin-Course

document.addEventListener('DOMContentLoaded', () => {
    // Elegant fade-in effect for glassmorphic cards on load
    const cards = document.querySelectorAll('.glass-card, .course-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(15px)';
        card.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 80);
    });
});
