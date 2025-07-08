document.addEventListener("DOMContentLoaded", () => {
  const user = JSON.parse(sessionStorage.getItem("user"));
  if (!user) return location.href = "login.html";

  const profileForm = document.getElementById("profileForm");
  const avatarForm = document.getElementById("avatarForm");
  const passwordForm = document.getElementById("passwordForm");

  const profileMsg = document.getElementById("profileMessage");
  const avatarMsg = document.getElementById("avatarMessage");
  const passwordMsg = document.getElementById("passwordMessage");
  const avatarPreview = document.getElementById("avatarPreview");

  // Charger les infos de profil
  fetch("../../api/user/get_profile.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ user_id: user.id })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const u = data.user;
        profileForm.first_name.value = u.first_name;
        profileForm.last_name.value = u.last_name;
        profileForm.email.value = u.email;
        avatarPreview.src = "../../uploads/avatars/" + u.avatar;
      }
    });

  // Mise à jour infos personnelles
  profileForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const updated = {
      user_id: user.id,
      first_name: profileForm.first_name.value.trim(),
      last_name: profileForm.last_name.value.trim()
    };

    fetch("../../api/user/update_profile.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(updated)
    })
      .then(res => res.json())
      .then(result => {
        profileMsg.textContent = result.message || result.error;
        profileMsg.style.color = result.success ? "green" : "red";
      });
  });

  // Mise à jour avatar
  avatarForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(avatarForm);
    formData.append("user_id", user.id);

    fetch("../../api/user/upload_avatar.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(result => {
        avatarMsg.textContent = result.message || result.error;
        avatarMsg.style.color = result.success ? "green" : "red";
        if (result.success) {
          avatarPreview.src = "../../uploads/avatars/" + result.avatar;
        }
      });
  });

  // Changement de mot de passe
  passwordForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const data = {
      user_id: user.id,
      current: passwordForm.current.value,
      new: passwordForm.new.value
    };

    fetch("../../api/user/update_password.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    })
      .then(res => res.json())
      .then(result => {
        passwordMsg.textContent = result.message || result.error;
        passwordMsg.style.color = result.success ? "green" : "red";
        if (result.success) passwordForm.reset();
      });
  });
});
