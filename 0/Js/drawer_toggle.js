document.addEventListener("DOMContentLoaded", async function () {
    const drawer = document.getElementById("drawer");
    const drawerIcons = document.querySelectorAll(".drawer_icon");
    let isDrawerOpen = false;

    function toggleDrawer() {
        isDrawerOpen = !isDrawerOpen;
        drawer.style.left = isDrawerOpen ? '0' : '-300px';
    }

    drawerIcons.forEach(icon => {
        icon.addEventListener("click", () => {
            toggleDrawer()
        });
    });


});