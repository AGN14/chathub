document.addEventListener("DOMContentLoaded", () => {
  handleRegisterForm();
  handleLoginForm();
  handleLogout();
  handleForgotPasswordUI();
});

// ----- INSCRIPTION -----
function handleRegisterForm() {
  const registerForm = document.getElementById("registerForm");
  const registerMsg = document.getElementById("registerMessage");

  if (!registerForm) return;

  registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const password = registerForm.password.value.trim();
    const confirmPassword = registerForm.confirm_password.value.trim();

    if (password !== confirmPassword) {
      showMessage(registerMsg, "Les mots de passe ne correspondent pas.", "error");
      return;
    }

    const data = {
      first_name: registerForm.first_name.value.trim(),
      last_name: registerForm.last_name.value.trim(),
      username: registerForm.username.value.trim(),
      email: registerForm.email.value.trim(),
      password,
      phone: registerForm.phone?.value.trim() || null,
      birth_date: registerForm.birth_date?.value || null,
      gender: registerForm.gender?.value || null,
      city: registerForm.city?.value.trim() || null,
      country: registerForm.country?.value.trim() || null,
      bio: registerForm.bio?.value.trim() || null,
      newsletter: registerForm.newsletter?.checked ? 1 : 0,
      terms: registerForm.terms?.checked ? 1 : 0,
    };

    if (!data.terms) {
      showMessage(registerMsg, "Vous devez accepter les conditions d'utilisation.", "error");
      return;
    }

    try {
      const res = await fetch("../../api/auth/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      });

      const result = await res.json();

      if (res.ok && result.success) {
        showMessage(registerMsg, result.message || "Inscription réussie.", "success");
        registerForm.reset();
      } else {
        showMessage(registerMsg, result.error || "Erreur lors de l'inscription.", "error");
      }
    } catch (err) {
      showMessage(registerMsg, "Erreur réseau.", "error");
    }
  });
}

// ----- CONNEXION -----
function handleLoginForm() {
  const loginForm = document.getElementById("loginForm");
  const loginMsg = document.getElementById("loginMessage");

  if (!loginForm) return;

  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = {
      email: loginForm.email.value.trim(),
      password: loginForm.password.value.trim(),
    };

    try {
      const res = await fetch("../../api/auth/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      });

      const result = await res.json();

      if (res.ok && result.success) {
        showMessage(loginMsg, "Connexion réussie ! Redirection...", "success");
        sessionStorage.setItem("user", JSON.stringify(result.user));
        setTimeout(() => window.location.href = "home.html", 1000);
      } else {
        showMessage(loginMsg, result.error || "Erreur de connexion.", "error");
      }
    } catch (err) {
      showMessage(loginMsg, "Erreur réseau.", "error");
    }
  });
}

// ----- DECONNEXION -----
function handleLogout() {
  const logoutBtn = document.getElementById("logoutBtn");

  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      sessionStorage.removeItem("user");
      window.location.href = "login.html";
    });
  }
}

// ----- FORMULAIRE MOT DE PASSE OUBLIÉ -----
function handleForgotPasswordUI() {
  const forgotLink = document.querySelector(".forgot-password");

  if (!forgotLink) return;

  forgotLink.addEventListener("click", (e) => {
    e.preventDefault();

    document.body.innerHTML = `
      <div class="auth-container" role="main">
        <div class="logo-container">
          <div class="logo">
            <div class="chat-bubble"></div>
          </div>
          <h1 class="logo-text">Chathub</h1>
        </div>

        <h2 class="auth-title">Mot de passe oublié</h2>

        <form id="loginForm" method="POST" action="../../api/auth/forgot_password.php" aria-label="Formulaire mot de passe oublié">
          <p id="loginMessage" role="alert" aria-live="assertive"></p>
          <input type="email" name="email" placeholder="Adresse e-mail" required>
          <button type="submit">Envoyer</button>
        </form>

        <p class="auth-footer">Pas encore de compte ? <a href="register.html">Créer un compte</a></p>
      </div>

      <script src="../../assets/js/auth.js" defer></script>
    `;
  });
}

// ----- AFFICHAGE DES MESSAGES -----
function showMessage(el, message, type = "neutral") {
  if (!el) return;

  el.textContent = message;
  el.className = ""; // Réinitialise les classes
  if (type === "success") el.classList.add("success-msg");
  else if (type === "error") el.classList.add("error-msg");
  else el.classList.add("neutral-msg");
}
