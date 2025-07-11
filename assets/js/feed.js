document.addEventListener("DOMContentLoaded", () => {
  const feedContainer = document.getElementById("feedContainer"); 
  const user = JSON.parse(sessionStorage.getItem("user"));

  if (!user) {
    window.location.href = "login.html";
    return;
  }

  // ----- Envoi de la publication -----
  document.querySelector("#createPostForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    formData.append("user_id", user.id);
    formData.append("content", document.getElementById("content").value);
    formData.append("image", document.getElementById("image").files[0]);

    try {
      const response = await fetch("http://localhost/chathub/api/posts/create_post.php", {
        method: "POST",
        body: formData
      });
      const result = await response.json();

      if (result.success) {
        document.getElementById("postMessage").textContent = "✅ Publication réussie.";
        e.target.reset();
        loadPosts(); // Recharger le fil
      } else {
        document.getElementById("postMessage").textContent = "❌ Échec de la publication.";
      }
    } catch (error) {
      document.getElementById("postMessage").textContent = "❌ Erreur réseau.";
    }
  });

  // ----- Chargement des publications -----
  async function loadPosts() {
    feedContainer.innerHTML = "<p>Chargement...</p>";

    try {
      const res = await fetch("../../api/posts/get_posts.php");
      const data = await res.json();

      if (!data.success) {
        feedContainer.textContent = "Erreur lors du chargement des posts.";
        return;
      }

      feedContainer.innerHTML = "";

      data.posts.forEach(post => {
        const template = document.getElementById("postTemplate");
        const clone = template.content.cloneNode(true);

        // Remplissage des infos
        clone.querySelector(".post-card").dataset.postId = post.id;
        clone.querySelector(".post-avatar").src = `../../uploads/avatars/${post.avatar}`;
        clone.querySelector(".post-author").textContent = `${post.first_name} ${post.last_name}`;
        clone.querySelector(".post-date").textContent = new Date(post.created_at).toLocaleString();
        clone.querySelector(".post-content").textContent = post.content || "";

        const imageContainer = clone.querySelector(".media-container");
        const postImage = clone.querySelector(".post-image");

        if (post.image) {
          postImage.src = `../../uploads/posts/${post.image}`;
          postImage.alt = `Image du post de ${post.first_name}`;
          imageContainer.style.display = "block";
        } else {
          imageContainer.style.display = "none";
        }

        clone.querySelector(".like-btn").textContent = `❤️ ${post.likes_count}`;
        clone.querySelector(".comment-btn").textContent = `💬 ${post.comments_count}`;

        // Ajout au DOM
        feedContainer.appendChild(clone);
      });

      setupEventListeners();
    } catch {
      feedContainer.textContent = "Erreur réseau.";
    }
  }

  // ----- Gestion des événements (likes, commentaires) -----
  function setupEventListeners() {
    document.querySelectorAll(".like-btn").forEach(button => {
      button.addEventListener("click", async (e) => {
        const postElem = e.target.closest(".post-card");
        const postId = postElem.dataset.postId;

        try {
          const res = await fetch("../../api/posts/like_post.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ post_id: postId, user_id: user.id })
          });

          const result = await res.json();
          if (res.ok && result.success) {
            e.target.textContent = `❤️ ${result.new_likes_count}`;
          } else {
            alert(result.error || "Erreur lors du like.");
          }
        } catch {
          alert("Erreur réseau.");
        }
      });
    });

    document.querySelectorAll(".comment-btn").forEach(button => {
      button.addEventListener("click", (e) => {
        const postElem = e.target.closest(".post-card");
        const postId = postElem.dataset.postId;
        // Tu peux ajouter ici la logique de commentaires dynamiques si besoin
        alert(`Zone de commentaires à implémenter pour post #${postId}`);
      });
    });
  }

  // Chargement initial
  loadPosts();
});
