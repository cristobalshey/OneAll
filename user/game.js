document.addEventListener("DOMContentLoaded", () => {
  const gameArea = document.getElementById("gameArea");
  const trashBin = document.getElementById("trashBin");
  const gameCoinDisplay = document.getElementById("gameCoin");
  const character = document.getElementById("character");
  const popup = document.getElementById("popup");
  const popupMessage = document.getElementById("popupMessage");

  let gameCoin = parseInt(gameCoinDisplay.textContent);

  const totalTrash = 10; // total trash items
  let trashCollected = 0;

  // ✅ Use FA6 class names
  const trashIcons = [
    { icon: "fa-solid fa-bottle-water", color: "#1E90FF", label: "Plastic" },
    { icon: "fa-solid fa-pizza-slice", color: "#FF5722", label: "Food" },
    { icon: "fa-solid fa-apple-whole", color: "#8BC34A", label: "Organic" },
    { icon: "fa-solid fa-file-lines", color: "#FBC02D", label: "Paper" },
    { icon: "fa-solid fa-cog", color: "#9E9E9E", label: "Metal" }
  ];

  // Function to spawn one trash item
  function spawnTrash() {
    const charRect = character.getBoundingClientRect();
    const areaRect = gameArea.getBoundingClientRect();
    const trashSize = 30;
    const data = trashIcons[Math.floor(Math.random() * trashIcons.length)];
    const trash = document.createElement("i");
    trash.className = `${data.icon} trash-item`;
    trash.style.color = data.color;

    // Safe zone to avoid spawning near character
    const SAFE_DISTANCE = 80; // px away from character center
    let left, top, safe = false, attempts = 0;
    const minTop = 40;
    const maxTop = gameArea.offsetHeight - 100;

    while (!safe && attempts < 50) {
      left = Math.random() * (gameArea.offsetWidth - trashSize - 20);
      top = minTop + Math.random() * (maxTop - minTop);

      const absLeft = areaRect.left + left;
      const absTop = areaRect.top + top;
      const trashRect = {
        left: absLeft,
        right: absLeft + trashSize,
        top: absTop,
        bottom: absTop + trashSize
      };

      // Character center
      const charCenterX = (charRect.left + charRect.right) / 2;
      const charCenterY = (charRect.top + charRect.bottom) / 2;
      // Trash center
      const trashCenterX = (trashRect.left + trashRect.right) / 2;
      const trashCenterY = (trashRect.top + trashRect.bottom) / 2;

      const distance = Math.sqrt(
        Math.pow(trashCenterX - charCenterX, 2) +
        Math.pow(trashCenterY - charCenterY, 2)
      );

      if (distance > SAFE_DISTANCE) safe = true;
      attempts++;
    }

    trash.style.left = left + "px";
    trash.style.top = top + "px";
    trash.draggable = true;

    // Drag events with debug logs
    trash.addEventListener("dragstart", e => {
      console.log("Dragging:", data.label);
      e.dataTransfer.setData("text/plain", "dragging");
      trash.classList.add("dragging");
      trashBin.src = "images/trashopen.png";
    });

    trash.addEventListener("dragend", () => {
      console.log("Drag end");
      trash.classList.remove("dragging");
      trashBin.src = "images/trashclose.png";
    });

    gameArea.appendChild(trash);
  }

  // Spawn all trash items
  for (let i = 0; i < totalTrash; i++) {
    spawnTrash();
  }

  // Popup
  function showPopup(message) {
    popupMessage.textContent = message;
    popup.style.display = "block";
  }

  window.closePopup = function () {
    popup.style.display = "none";
  };

  // Trash bin drag events
  trashBin.addEventListener("dragover", e => e.preventDefault());
  trashBin.addEventListener("dragenter", () => {
    trashBin.src = "images/trashopen.png";
  });
  trashBin.addEventListener("dragleave", () => {
    trashBin.src = "images/trashclose.png";
  });
  trashBin.addEventListener("drop", e => {
    e.preventDefault();
    const dragging = document.querySelector(".dragging");
    if (dragging) {
      dragging.remove();
      trashCollected++;
      trashBin.src = "images/trashclose.png";

      if (trashCollected >= totalTrash) {
        gameCoin += 10;
        gameCoinDisplay.textContent = gameCoin;
        showPopup("All trash collected! +10 Bonus Coins");
        updateEngagement(gameCoin);
      }
    }
  });
});

// Engagement Level Function
function updateEngagement(points) {
  let title = "Beginner";
  if (points >= 100 && points < 500) title = "Developing";
  else if (points >= 500 && points < 1000) title = "Active Learner";
  else if (points >= 1000) title = "Master";
  document.getElementById("engagementTitle").innerText = title;
}
