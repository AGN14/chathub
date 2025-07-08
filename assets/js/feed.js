document.addEventListener("DOMContentLoaded", () => {
  const feedContainer = document.getElementById("feedContainer"); 
  const user = JSON.parse(sessionStorage.getItem("user"));

  if (!user) {
    window.location.href = "login.html";
    return;
  }

  //-----Envoie de la publication -----
  
  document.querySelector("#createPostForm").addEventListener("submit", async (e) => {
    e.preventDefault(); // Empêche le rechargement de la page

    const formData = new FormData(e.target); // Récupère les données du formulaire
    formData.append("user_id", user.id); // Ajoute l'ID de l'utilisateur
    formData.append("content", document.getElementById("content").value);
    formData.append("image", document.getElementById("image").files[0]); // Fichier image

    fetch("http://localhost/chathub/api/posts/create_post.php", {
        method: "POST",
        body: formData // FORMDATA se charge de l'encodage multipart/form-data
    })
    .then(response => console.log("Post envoyé avec succès !"))
    .catch(error => console.log("Erreur lors de l'envoi du post :", error))
  })



  async function loadPosts() {
    feedContainer.innerHTML = "<p>Chargement...</p>";

    // Recuperation et affichage des publications depuis l'API
    try {
      const res = await fetch("../../api/posts/get_posts.php");
      const data = await res.json();

      if (!data.success) {
        feedContainer.textContent = "Erreur lors du chargement des posts.";
        return;
      }

      feedContainer.innerHTML = "";

      data.posts.forEach(post => {
        const postElem = document.createElement("article");
        postElem.classList.add("post");
        postElem.dataset.postId = post.id;

        postElem.innerHTML = `
          <div class="post-header">
            <img src="../../uploads/avatars/${post.avatar}" alt="Avatar" class="avatar" />
            <span class="author">${post.first_name} ${post.last_name}</span>
            <time>${new Date(post.created_at).toLocaleString()}</time>
          </div>
          <p class="post-content">${post.content || ""}</p>
          ${post.image ? `<img src="../../uploads/posts/${post.image}" alt="Image post" class="post-image" />` : ""}
          <div class="post-footer">
            <button class="like-btn">❤️ ${post.likes_count}</button>
            <button class="comment-toggle-btn">💬 ${post.comments_count}</button>
          </div>
          <div class="comments-section" style="display:none;">
            <div class="comments-list"></div>
            <form class="comment-form">
              <input type="text" name="comment" placeholder="Ajouter un commentaire..." required />
              <button type="submit">Envoyer</button>
            </form>
          </div>
        `;

        feedContainer.appendChild(postElem);
      });

      setupEventListeners();
    } catch {
      feedContainer.textContent = "Erreur réseau.";
    }
  }

  // ---- Gestion des événements (likes + commentaires) ----
  function setupEventListeners() {
    // Boutons de like
    document.querySelectorAll(".like-btn").forEach(button => {
      button.addEventListener("click", async (e) => {
        const postElem = e.target.closest(".post");
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

    // Boutons de commentaires
    document.querySelectorAll(".comment-toggle-btn").forEach(button => {
      button.addEventListener("click", async (e) => {
        const postElem = e.target.closest(".post");
        const commentsSection = postElem.querySelector(".comments-section");
        commentsSection.style.display = commentsSection.style.display === "none" ? "block" : "none";

        if (commentsSection.style.display === "block") {
          loadComments(postElem.dataset.postId, commentsSection.querySelector(".comments-list"));
        }
      });
    });

    // Formulaires d'ajout de commentaire
    document.querySelectorAll(".comment-form").forEach(form => {
      form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const postElem = e.target.closest(".post");
        const postId = postElem.dataset.postId;
        const commentInput = form.comment;
        const commentText = commentInput.value.trim();

        if (!commentText) return;

        try {
          const res = await fetch("../../api/posts/comment_post.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ post_id: postId, user_id: user.id, content: commentText })
          });

          const result = await res.json();

          if (res.ok && result.success) {
            commentInput.value = "";
            loadComments(postId, postElem.querySelector(".comments-list"));
            const commentBtn = postElem.querySelector(".comment-toggle-btn");
            commentBtn.textContent = `💬 ${result.new_comments_count}`;
          } else {
            alert(result.error || "Erreur lors de l'envoi du commentaire.");
          }
        } catch {
          alert("Erreur réseau.");
        }
      });
    });
  }

  // ---- Chargement des commentaires pour un post donné ----
  async function loadComments(postId, container) {
    container.innerHTML = "<p>Chargement des commentaires...</p>";

    try {
      const res = await fetch(`../../api/posts/get_comments.php?post_id=${postId}`);
      const data = await res.json();

      if (!data.success) {
        container.textContent = "Erreur lors du chargement des commentaires.";
        return;
      }

      container.innerHTML = "";

      data.comments.forEach(comment => {
        const c = document.createElement("div");
        c.classList.add("comment");
        c.innerHTML = `
          <strong>${comment.first_name} ${comment.last_name}</strong> :
          <span>${comment.content}</span>
          <time>${new Date(comment.created_at).toLocaleString()}</time>
        `;
        container.appendChild(c);
      });

    } catch {
      container.textContent = "Erreur réseau.";
    }
  }

  // Lancement initial
  loadPosts();
});
