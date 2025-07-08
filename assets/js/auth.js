document.addEventListener("DOMContentLoaded", () => {
  // ---------- INSCRIPTION ----------
  const registerForm = document.getElementById("registerForm");
  const registerMsg = document.getElementById("registerMessage"); 

  if (registerForm) {
    registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const password = registerForm.password.value.trim();
      const confirmPassword = registerForm.confirm_password.value.trim();

      if (password !== confirmPassword) {
        registerMsg.textContent = "Les mots de passe ne correspondent pas.";
        registerMsg.style.color = "red";
        return;
      }

      const data = {
        first_name: registerForm.first_name.value.trim(),
        last_name: registerForm.last_name.value.trim(),
        username: registerForm.username.value.trim(),
        email: registerForm.email.value.trim(),
        password: password,
        phone: registerForm.phone?.value.trim() || null,
        birth_date: registerForm.birth_date?.value || null,
        gender: registerForm.gender?.value || null,
        city: registerForm.city?.value.trim() || null,
        country: registerForm.country?.value.trim() || null,
        bio: registerForm.bio?.value.trim() || null,
        newsletter: registerForm.newsletter?.checked ? 1 : 0,
        terms: registerForm.terms?.checked ? 1 : 0
      };

      if (!data.terms) {
        registerMsg.textContent = "Vous devez accepter les conditions d'utilisation.";
        registerMsg.style.color = "red";
        return;
      }

      try {
        const res = await fetch("../../api/auth/register.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data)
        });

        const result = await res.json();

        if (res.ok && result.success) {
          registerMsg.textContent = result.message || "Inscription réussie.";
          registerMsg.style.color = "green";
          registerForm.reset();
        } else {
          registerMsg.textContent = result.error || "Erreur lors de l’inscription.";
          registerMsg.style.color = "red";
        }
      } catch (err) {
        registerMsg.textContent = "Erreur réseau.";
        registerMsg.style.color = "red";
      }
    });
  }

  // ---------- CONNEXION ----------
  const loginForm = document.getElementById("loginForm");
  const loginMsg = document.getElementById("loginMessage");

  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const data = {
        email: loginForm.email.value.trim(),
        password: loginForm.password.value.trim()
      };

      try {
        const res = await fetch("../../api/auth/login.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data)
        });

        const result = await res.json();

        if (res.ok && result.success) {
          loginMsg.textContent = "Connexion réussie ! Redirection...";
          loginMsg.style.color = "green";

          sessionStorage.setItem("user", JSON.stringify(result.user));

          setTimeout(() => {
            window.location.href = "home.html";
          }, 1000);
        } else {
          loginMsg.textContent = result.error || "Erreur de connexion.";
          loginMsg.style.color = "red";
        }
      } catch (err) {
        loginMsg.textContent = "Erreur réseau.";
        loginMsg.style.color = "red";
      }
    });
  }

  // ---------- DECONNEXION ----------
  const logoutBtn = document.getElementById("logoutBtn");

  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      sessionStorage.removeItem("user");
      window.location.href = "login.html";
    });
  }
});

if(document.querySelector(".forgot-password")){
  const pwd_forgot = document.querySelector(".forgot-password"); 

pwd_forgot.addEventListener("click", (e) => {
  e.preventDefault();
  document.querySelector("body").innerHTML = ` 
  <div class="auth-container">
    <div class="logo-container">
      <div class="logo">
        <div class="chat-bubble"></div>
      </div>
      <h1 class="logo-text">Chathub</h1>
    </div>
    
    <h2> Azy !</h2>
    <form id="loginForm" method="POST" action="../../api/auth/forgot_password.php">
    <p id="loginMessage"></p>
      <input type="email" name="email" placeholder="Adresse e-mail" required>
      <button type="submit">Envoyer</button>
    </form>
    <p class="auth-footer">Pas encore de compte ? <a href="register.html">Créer un compte</a></p>
  </div>
  <script src="../../assets/js/auth.js"></script>
  `
})
}