document.addEventListener("DOMContentLoaded", () => {
  const user = JSON.parse(sessionStorage.getItem("user"));
  if (!user) return location.href = "login.html";

  const conversationsList = document.getElementById("conversationsList");
  const messagesContainer = document.getElementById("messagesContainer");
  const messageForm = document.getElementById("messageForm");
  const messageInput = messageForm.querySelector("input[name='message']");

  let currentConversationId = null;
  let pollingInterval = null;

  // Charger la liste des conversations
  async function loadConversations() {
    try {
      const res = await fetch("../../api/chat/get_conversations.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_id: user.id }),
      });
      const data = await res.json();

      if (!data.success) {
        conversationsList.textContent = "Erreur lors du chargement des conversations.";
        return;
      }

      conversationsList.innerHTML = "";
      data.conversations.forEach(conv => {
        const li = document.createElement("li");
        li.textContent = conv.name; // ou autre info (ex: prénom interlocuteur)
        li.dataset.conversationId = conv.id;
        li.classList.add("conversation-item");
        li.addEventListener("click", () => {
          selectConversation(conv.id);
        });
        conversationsList.appendChild(li);
      });
    } catch (err) {
      conversationsList.textContent = "Erreur réseau.";
    }
  }

  // Sélectionner une conversation, charger ses messages
  async function selectConversation(conversationId) {
    currentConversationId = conversationId;

    // Mettre à jour l'UI pour indiquer la conversation sélectionnée
    document.querySelectorAll(".conversation-item").forEach(item => {
      item.classList.toggle("active", item.dataset.conversationId == conversationId);
    });

    await loadMessages(conversationId);

    // Setup polling pour actualiser les messages toutes les 5 secondes
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(() => loadMessages(conversationId), 5000);
  }

  // Charger les messages d'une conversation
  async function loadMessages(conversationId) {
    try {
      const res = await fetch(`../../api/chat/get_messages.php?conversation_id=${conversationId}&user_id=${user.id}`);
      const data = await res.json();

      if (!data.success) {
        messagesContainer.textContent = "Erreur lors du chargement des messages.";
        return;
      }

      messagesContainer.innerHTML = "";
      data.messages.forEach(msg => {
        const div = document.createElement("div");
        div.classList.add("message");
        if (msg.user_id === user.id) div.classList.add("mine");
        else div.classList.add("theirs");

        div.innerHTML = `
          <p>${msg.content}</p>
          <time>${new Date(msg.created_at).toLocaleTimeString()}</time>
        `;

        messagesContainer.appendChild(div);
      });

      // Scroll en bas
      messagesContainer.scrollTop = messagesContainer.scrollHeight;

    } catch (err) {
      messagesContainer.textContent = "Erreur réseau.";
    }
  }

  // Envoyer un message
  messageForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const content = messageInput.value.trim();
    if (!content || !currentConversationId) return;

    try {
      const res = await fetch("../../api/chat/send_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          conversation_id: currentConversationId,
          user_id: user.id,
          content
        })
      });
      const data = await res.json();

      if (data.success) {
        messageInput.value = "";
        await loadMessages(currentConversationId);
      } else {
        alert(data.error || "Erreur lors de l'envoi du message.");
      }
    } catch {
      alert("Erreur réseau.");
    }
  });

  // Initialisation
  loadConversations();
});
