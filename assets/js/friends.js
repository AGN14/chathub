document.addEventListener("DOMContentLoaded", () => {
  const user = JSON.parse(sessionStorage.getItem("user"));
  if (!user) return window.location.href = "login.html";

  const userList = document.getElementById("userList");
  const requestsList = document.getElementById("requestsList");
  const friendsList = document.getElementById("friendsList");

  // Lister les utilisateurs
  fetch("../../api/friends/get_users.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ user_id: user.id })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      data.users.forEach(u => {
        const div = document.createElement("div");
        div.innerHTML = `
          <strong>${u.first_name} ${u.last_name}</strong>
          <button data-id="${u.id}">Ajouter</button>
        `;
        div.querySelector("button").addEventListener("click", () => sendRequest(u.id));
        userList.appendChild(div);
      });
    }
  });

  // Envoyer une demande
  function sendRequest(receiverId) {
    fetch("../../api/friends/send_request.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ sender_id: user.id, receiver_id: receiverId })
    })
    .then(res => res.json())
    .then(result => alert(result.message || result.error));
  }

  // Voir les demandes reçues
  fetch("../../api/friends/get_requests.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ user_id: user.id })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      data.requests.forEach(req => {
        const div = document.createElement("div");
        div.innerHTML = `
          <strong>${req.first_name} ${req.last_name}</strong>
          <button data-id="${req.id}" class="accept">Accepter</button>
          <button data-id="${req.id}" class="refuse">Refuser</button>
        `;
        div.querySelector(".accept").addEventListener("click", () => handleRequest(req.id, "accept"));
        div.querySelector(".refuse").addEventListener("click", () => handleRequest(req.id, "refuse"));
        requestsList.appendChild(div);
      });
    }
  });

  function handleRequest(requestId, action) {
    fetch(`../../api/friends/${action}_request.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ request_id: requestId })
    })
    .then(res => res.json())
    .then(result => {
      alert(result.message || result.error);
      window.location.reload();
    });
  }

  // Voir les amis
  fetch("../../api/friends/get_friends.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ user_id: user.id })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      data.friends.forEach(friend => {
        const div = document.createElement("div");
        div.innerHTML = `<strong>${friend.first_name} ${friend.last_name}</strong>`;
        friendsList.appendChild(div);
      });
    }
  });
});
