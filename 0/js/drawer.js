document.addEventListener("DOMContentLoaded", async function () {
    const drawer = document.getElementById("drawer");
    const drawerIcons = document.querySelectorAll(".drawer_icon");
    let isDrawerOpen = false;

   
    drawerIcons.forEach(icon => {
        icon.addEventListener("click", () => {
            toggleDrawer()
        });
    });



});


function toggleDrawer() {
    document.querySelector('.drawer').classList.toggle('open');
    document.querySelector('.main-content').classList.toggle('drawer-open');
}

function toggleSubmenu(event) {
    event.preventDefault();
    const parent = event.target.closest('.has-submenu');
    const arrow = parent.querySelector('.arrow');

    parent.classList.toggle('open');

    // Mostra/esconde o submenu
    const submenu = parent.querySelector('.submenu');
    submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';

    // Atualiza o texto da seta
    if (submenu.style.display === 'block') {
        arrow.textContent = '▲'; // Submenu aberto
    } else {
        arrow.textContent = '▼'; // Submenu fechado
    }
}