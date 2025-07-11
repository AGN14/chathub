document.addEventListener("DOMContentLoaded", () => {
  const user = JSON.parse(sessionStorage.getItem("user"));
  if (!user) {
    window.location.href = "login.html";
    return;
  }

  // Affiche l'avatar et nom dans la sidebar
  const avatarEl = document.getElementById("sidebarUserAvatar");
  const nameEl = document.getElementById("sidebarUserName");

  avatarEl.src = user.avatar ? `http://localhost/chathub/uploads/avatars/${user.avatar}` : "http://localhost/chathub/uploads/avatars/default-avatar.png";
  nameEl.textContent = user.first_name + " " + user.last_name;

  // Déconnexion
  const logoutSidebarBtn = document.getElementById("logoutBtnSidebar");
  logoutSidebarBtn.addEventListener("click", (e) => {
    e.preventDefault();
    sessionStorage.removeItem("user");
    window.location.href = "login.html";
  });
});
