document.addEventListener("DOMContentLoaded", () => {
  // Activation lien actif sidebar
  const currentPage = window.location.pathname.split("/").pop();
  document.querySelectorAll(".sidebar nav a").forEach(link => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active");
    }
  });

  // Confirmation suppression boutons
  document.querySelectorAll(".btn-delete").forEach(btn => {
    btn.addEventListener("click", e => {
      if (!confirm("Êtes-vous sûr de vouloir supprimer cet élément ?")) {
        e.preventDefault();
      }
    });
  });

  // Affichage messages alertes
  window.showAlert = function(message, type = "success") {
    let alertBox = document.createElement("div");
    alertBox.className = "alert alert-" + (type === "success" ? "success" : "error");
    alertBox.textContent = message;
    alertBox.style.position = "fixed";
    alertBox.style.top = "1rem";
    alertBox.style.right = "1rem";
    alertBox.style.zIndex = 1000;
    alertBox.style.padding = "1rem 1.5rem";
    alertBox.style.borderRadius = "5px";
    alertBox.style.boxShadow = "0 2px 8px rgba(0,0,0,0.15)";
    alertBox.style.color = type === "success" ? "#065f46" : "#b91c1c";
    alertBox.style.backgroundColor = type === "success" ? "#d1fae5" : "#fee2e2";
    document.body.appendChild(alertBox);

    setTimeout(() => {
      alertBox.remove();
    }, 4000);
  };

  // Bouton déconnexion admin
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      if (confirm("Voulez-vous vraiment vous déconnecter ?")) {
        fetch("../../api/auth/logout.php", {
          method: "POST",
          credentials: "include"
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              window.location.href = "admin-login.html";
            } else {
              showAlert("Erreur lors de la déconnexion", "error");
            }
          })
          .catch(() => showAlert("Erreur réseau", "error"));
      }
    });
  }
});
