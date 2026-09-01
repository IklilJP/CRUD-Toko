import './bootstrap';

document.querySelectorAll('[data-flash]').forEach((el) => {
    setTimeout(() => el.remove(), 3000);
});
