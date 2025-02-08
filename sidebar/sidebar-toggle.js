
// toggle sidebar
const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const header = document.getElementById('header');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
    if (sidebar.classList.contains('collapsed')) {
        header.style.backgroundColor = '#242426';  
    } else {
        header.style.backgroundColor = '#1c1c1e';
    }
});