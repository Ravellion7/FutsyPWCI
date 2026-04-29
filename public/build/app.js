// ============================================================
// Mobile Hamburger Menu Toggle
// ============================================================

const initHamburgerMenu = () => {
    const hamburgerBtn = document.getElementById('hamburgerToggle');
    const mobileMenu = document.getElementById('mobileMenu');

    if (!hamburgerBtn || !mobileMenu) return; // Exit if elements don't exist (e.g., on auth pages)

    // Toggle menu visibility on button click
    hamburgerBtn.addEventListener('click', () => {
        const isHidden = mobileMenu.classList.contains('hidden');
        if (isHidden) {
            mobileMenu.classList.remove('hidden');
            mobileMenu.classList.add('flex');
        } else {
            mobileMenu.classList.add('hidden');
            mobileMenu.classList.remove('flex');
        }
    });

    // Close menu when a link is clicked
    const mobileLinks = mobileMenu.querySelectorAll('a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
            mobileMenu.classList.remove('flex');
        });
    });
};

// Initialize hamburger menu when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHamburgerMenu);
} else {
    initHamburgerMenu();
}

// ============================================================
// API Helper Functions
// ============================================================

(function () {
  const PACK_ID = 1;

  async function apiRequest(url, options) {
    const response = await fetch(url, {
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json"
      },
      ...options
    });

    let data = {};
    try {
      data = await response.json();
    } catch (error) {
      data = {
        ok: false,
        error: "Invalid JSON response from server."
      };
    }

    return {
      status: response.status,
      data
    };
  }

  function setText(element, text) {
    if (!element) {
      return;
    }
    element.textContent = text;
  }

  function setBusy(button, busy, busyText, idleText) {
    if (!button) {
      return;
    }

    button.disabled = busy;
    button.classList.toggle("opacity-60", busy);
    button.classList.toggle("cursor-not-allowed", busy);
    button.textContent = busy ? busyText : idleText;
  }

  function formatCooldown(seconds) {
    const safeSeconds = Math.max(0, Number(seconds) || 0);
    const hours = Math.floor(safeSeconds / 3600);
    const minutes = Math.floor((safeSeconds % 3600) / 60);
    const remainingSeconds = safeSeconds % 60;

    return hours + "h " + minutes + "m " + remainingSeconds + "s";
  }

  async function initHomePage() {
    const claimButton = document.getElementById("claimDailyBtn");
    const claimStatus = document.getElementById("claimDailyStatus");

    if (!claimButton || !claimStatus) {
      return;
    }

    claimButton.addEventListener("click", async function () {
      setBusy(claimButton, true, "Reclamando...", "Reclamar pack diario");
      setText(claimStatus, "Procesando solicitud...");

      try {
        const result = await apiRequest("/api/packs/claim-daily", {
          method: "POST",
          body: "{}"
        });

        if (result.data.ok) {
          const packsBalance = result.data.packs_balance;
          setText(claimStatus, "Pack diario reclamado. Packs disponibles: " + packsBalance + ".");
          return;
        }

        const cooldown = result.data.seconds_until_next_claim;
        if (typeof cooldown !== "undefined") {
          setText(
            claimStatus,
            "Todavia no puedes reclamar otro pack. Tiempo restante: " + formatCooldown(cooldown) + "."
          );
        } else {
          setText(claimStatus, result.data.error || "No se pudo reclamar el pack diario.");
        }
      } catch (error) {
        setText(claimStatus, "Error de red al reclamar el pack diario.");
      } finally {
        setBusy(claimButton, false, "Reclamando...", "Reclamar pack diario");
      }
    });
  }



  function wait(ms) {
    return new Promise(function (resolve) {
      window.setTimeout(resolve, ms);
    });
  }

  async function refreshPacksBalance(balanceElement, statusElement) {
    const result = await apiRequest("/api/packs/balance", {
      method: "GET"
    });

    if (result.data.ok && result.data.data) {
      setText(balanceElement, String(result.data.data.packs_balance));
      return true;
    }

    setText(statusElement, result.data.error || "No se pudo cargar el balance de packs.");
    return false;
  }

  async function initMarketPage() {
    const openOneButton = document.getElementById("abrirUno");
    const openAllButton = document.getElementById("abrirTodos");
    const balanceValue = document.getElementById("packsBalanceValue");
    const statusText = document.getElementById("marketStatus");

    const revealModal = document.getElementById("packRevealModal");
    const revealLoadingState = document.getElementById("revealLoadingState");
    const revealLoadingText = document.getElementById("revealLoadingText");
    const revealCardsState = document.getElementById("revealCardsState");
    const revealCounter = document.getElementById("revealCounter");
    const revealFrame = document.getElementById("revealCardFrame");
    const revealImage = document.getElementById("revealCardImage");
    const revealName = document.getElementById("revealCardName");
    const revealMeta = document.getElementById("revealCardMeta");
    const revealPrevCardBtn = document.getElementById("revealPrevCardBtn");
    const revealNextCardBtn = document.getElementById("revealNextCardBtn");
    const acceptCardsButton = document.getElementById("acceptCardsBtn");
    const openAnotherPackButton = document.getElementById("openAnotherPackBtn");

    if (!openOneButton || !openAllButton || !balanceValue || !statusText) {
      return;
    }

    let currentPacksBalance = 0;
    let revealRunning = false;
    let revealedCards = [];
    let currentRevealIndex = 0;

    function updateArrowButtons() {
      if (!revealPrevCardBtn || !revealNextCardBtn) {
        return;
      }

      const hasCards = revealedCards.length > 0;
      const disablePrev = !hasCards || revealRunning || currentRevealIndex <= 0;
      const disableNext = !hasCards || revealRunning || currentRevealIndex >= revealedCards.length - 1;

      revealPrevCardBtn.disabled = disablePrev;
      revealPrevCardBtn.classList.toggle("opacity-50", disablePrev);
      revealPrevCardBtn.classList.toggle("cursor-not-allowed", disablePrev);

      revealNextCardBtn.disabled = disableNext;
      revealNextCardBtn.classList.toggle("opacity-50", disableNext);
      revealNextCardBtn.classList.toggle("cursor-not-allowed", disableNext);
    }

    function displayRevealCard(index, withAnimation) {
      if (!revealCounter || !revealFrame || !revealImage || !revealName || !revealMeta) {
        return;
      }

      if (!Array.isArray(revealedCards) || revealedCards.length === 0) {
        return;
      }

      const safeIndex = Math.max(0, Math.min(index, revealedCards.length - 1));
      currentRevealIndex = safeIndex;
      const card = revealedCards[safeIndex] || {};

      revealCounter.textContent = "Carta " + (safeIndex + 1) + " de " + revealedCards.length;
      revealName.textContent = card.player_name || ("Card #" + (card.id_card || "-"));
      revealMeta.textContent =
        (card.rarity || "Unknown") +
        (card.team_name ? (" - " + card.team_name) : "");
      revealImage.src = card.image_url || "/img/image3.png";

      if (withAnimation) {
        revealFrame.style.opacity = "0";
        revealFrame.style.transform = "scale(0.92)";
        window.setTimeout(function () {
          revealFrame.style.opacity = "1";
          revealFrame.style.transform = "scale(1)";
        }, 120);
      } else {
        revealFrame.style.opacity = "1";
        revealFrame.style.transform = "scale(1)";
      }

      updateArrowButtons();
    }

    function closeRevealModal() {
      if (!revealModal) {
        return;
      }
      revealModal.style.display = "none";
      document.body.style.overflow = "";
    }

    function openRevealModal() {
      if (!revealModal) {
        return;
      }
      revealModal.style.display = "block";
      document.body.style.overflow = "hidden";
    }

    function showLoadingState(text) {
      if (revealLoadingState) {
        revealLoadingState.style.display = "block";
      }

      if (revealCardsState) {
        revealCardsState.style.display = "none";
      }

      if (revealLoadingText) {
        revealLoadingText.textContent = text || "Abriendo pack...";
      }
    }

    function showCardsState() {
      if (revealLoadingState) {
        revealLoadingState.style.display = "none";
      }

      if (revealCardsState) {
        revealCardsState.style.display = "block";
      }
    }

    async function revealCardsSequentially(cards) {
      if (!revealModal || !revealCounter || !revealFrame || !revealImage || !revealName || !revealMeta) {
        return;
      }

      revealedCards = Array.isArray(cards) ? cards : [];
      if (revealedCards.length === 0) {
        return;
      }

      showCardsState();
      revealRunning = true;
      updateArrowButtons();

      for (let index = 0; index < revealedCards.length; index++) {
        displayRevealCard(index, true);
        await wait(950);
      }

      revealRunning = false;
      updateArrowButtons();
    }

    async function updateBalanceFromApi() {
      const ok = await refreshPacksBalance(balanceValue, statusText);
      if (ok) {
        currentPacksBalance = Number(balanceValue.textContent || 0);
      }
    }

    async function handleOpenOne() {
      setBusy(openOneButton, true, "Abriendo...", "Abrir uno");
      setBusy(openAllButton, true, "Abriendo...", "Abrir todos");
      openRevealModal();
      showLoadingState("Abriendo pack...");

      try {
        const result = await apiRequest("/api/packs/open", {
          method: "POST",
          body: JSON.stringify({ pack_id: PACK_ID })
        });

        if (result.data.ok) {
          const payload = result.data.data || {};
          if (typeof payload.packs_balance !== "undefined") {
            currentPacksBalance = Number(payload.packs_balance || 0);
            setText(balanceValue, String(currentPacksBalance));
          } else {
            await updateBalanceFromApi();
          }

          await revealCardsSequentially(payload.cards || []);

          if (openAnotherPackButton) {
            openAnotherPackButton.disabled = currentPacksBalance < 1;
            openAnotherPackButton.classList.toggle("opacity-60", currentPacksBalance < 1);
            openAnotherPackButton.classList.toggle("cursor-not-allowed", currentPacksBalance < 1);
          }
        } else {
          closeRevealModal();
          setText(statusText, result.data.error || "No se pudo abrir el pack.");
        }
      } catch (error) {
        closeRevealModal();
        setText(statusText, "Error de red al abrir un pack.");
      } finally {
        setBusy(openOneButton, false, "Abriendo...", "Abrir uno");
        setBusy(openAllButton, false, "Abriendo...", "Abrir todos");
      }
    }

    await updateBalanceFromApi();

    if (acceptCardsButton) {
      acceptCardsButton.addEventListener("click", function () {
        if (!revealRunning) {
          closeRevealModal();
        }
      });
    }

    if (revealPrevCardBtn) {
      revealPrevCardBtn.addEventListener("click", function () {
        if (revealRunning || currentRevealIndex <= 0) {
          return;
        }
        displayRevealCard(currentRevealIndex - 1, true);
      });
    }

    if (revealNextCardBtn) {
      revealNextCardBtn.addEventListener("click", function () {
        if (revealRunning || currentRevealIndex >= revealedCards.length - 1) {
          return;
        }
        displayRevealCard(currentRevealIndex + 1, true);
      });
    }

    if (openAnotherPackButton) {
      openAnotherPackButton.addEventListener("click", async function () {
        if (revealRunning || currentPacksBalance < 1) {
          return;
        }
        closeRevealModal();
        await handleOpenOne();
      });
    }

    openOneButton.addEventListener("click", handleOpenOne);

    openAllButton.addEventListener("click", async function () {
      setBusy(openAllButton, true, "Abriendo...", "Abrir todos");
      setBusy(openOneButton, true, "Abriendo...", "Abrir uno");
      setText(statusText, "Abriendo todos los packs...");
      openRevealModal();
      showLoadingState("Abriendo todos los packs...");

      try {
        const result = await apiRequest("/api/packs/open-all", {
          method: "POST",
          body: JSON.stringify({ pack_id: PACK_ID })
        });

        if (result.data.ok) {
          const payload = result.data.data || {};
          const openedCount = payload.opened_count || 0;
          setText(statusText, "Se abrieron " + openedCount + " packs correctamente.");

          if (typeof payload.packs_balance !== "undefined") {
            currentPacksBalance = Number(payload.packs_balance || 0);
            setText(balanceValue, String(currentPacksBalance));
          } else {
            await updateBalanceFromApi();
          }
          closeRevealModal();

        } else {
          closeRevealModal();
          setText(statusText, result.data.error || "No se pudieron abrir todos los packs.");
        }
      } catch (error) {
        closeRevealModal();
        setText(statusText, "Error de red al abrir todos los packs.");
      } finally {
        setBusy(openAllButton, false, "Abriendo...", "Abrir todos");
        setBusy(openOneButton, false, "Abriendo...", "Abrir uno");
      }
    });
  }

  function buildCardTile(card, selected, showQuantity) {
    const selectedClass = selected ? "border-[#A4C351] ring-2 ring-[#A4C351]" : "border-transparent";
    const quantityText = showQuantity ? ("x" + (Number(card.quantity || 0) || 0)) : "";
    const name = card.player_name || ("Card #" + (card.id_card || "-"));
    const team = card.team_name || "Sin equipo";
    const rarity = card.rarity || "Unknown";

    return (
      "<button type=\"button\" data-card-id=\"" + card.id_card + "\" class=\"trade-card-tile w-24 h-36 sm:w-28 sm:h-40 rounded-md overflow-hidden relative border bg-[#0a5053] " + selectedClass + "\">" +
      "<img src=\"" + (card.image_url || "/img/image3.png") + "\" alt=\"" + name.replace(/\"/g, "") + "\" class=\"w-full object-cover\" style=\"height:75%; display:block;\">" +
      (showQuantity ? "<span class=\"absolute text-[10px] bg-black/70 text-white px-1 rounded\" style=\"left:4px; bottom:28%;\">" + quantityText + "</span>" : "") +
      "<span class=\"block text-[11px] leading-4 text-white px-1 py-1 truncate\" style=\"height:25%;\">" + name + "</span>" +
      "<span class=\"hidden\">" + team + " " + rarity + "</span>" +
      "</button>"
    );
  }

  function renderSelectedOfferGrid(container, selectedMap) {
    if (!container) {
      return;
    }

    const values = Object.values(selectedMap);
    if (values.length === 0) {
      container.innerHTML = "<div class=\"col-span-4 h-14 rounded-sm bg-[#E8DDD7]/30\"></div>";
      return;
    }

    container.innerHTML = values
      .slice(0, 16)
      .map(function (card) {
        const maxQuantity = Number(card.max_quantity || 1);
        const currentQuantity = Number(card.quantity || 1);
        const plusDisabled = currentQuantity >= maxQuantity ? "disabled" : "";

        return (
          "<div class=\"relative\">" +
          buildCardTile(card, true, false) +
          "<div class=\"absolute top-1 right-1 flex items-center gap-1\">" +
          "<button type=\"button\" class=\"trade-qty-btn px-1 rounded bg-black/70 text-white\" data-op=\"dec\" data-card-id=\"" + card.id_card + "\">-</button>" +
          "<button type=\"button\" class=\"trade-qty-btn px-1 rounded bg-black/70 text-white\" data-op=\"inc\" data-card-id=\"" + card.id_card + "\" " + plusDisabled + ">+</button>" +
          "</div>" +
          "<span class=\"absolute bottom-1 left-1 text-[10px] bg-black/70 text-white px-1 rounded\">x" + currentQuantity + "</span>" +
          "</div>"
        );
      })
      .join("");
  }

  function renderSelectedRequestGrid(container, selectedMap) {
    if (!container) {
      return;
    }

    const values = Object.values(selectedMap);
    if (values.length === 0) {
      container.innerHTML = "<div class=\"col-span-4 h-14 rounded-sm bg-[#E8DDD7]/30\"></div>";
      return;
    }

    container.innerHTML = values
      .slice(0, 16)
      .map(function (card) {
        return buildCardTile(card, true, false);
      })
      .join("");
  }

  async function initTradeBuilderPage() {
    const leftGrid = document.getElementById("tradeLeftGrid");
    const leftPrev = document.getElementById("tradeLeftPrev");
    const leftNext = document.getElementById("tradeLeftNext");
    const leftPageLabel = document.getElementById("tradeLeftPageLabel");
    const searchInput = document.getElementById("tradeCardSearch");
    const selectionTitle = document.getElementById("tradeSelectionTitle");
    const leftAccept = document.getElementById("tradeLeftAccept");
    const leftBack = document.getElementById("tradeLeftBack");
    const status = document.getElementById("tradeBuilderStatus");
    const offerGrid = document.getElementById("tradeOfferGrid");
    const requestGrid = document.getElementById("tradeRequestGrid");
    const submitButton = document.getElementById("ofertar");

    if (!leftGrid || !leftPrev || !leftNext || !leftPageLabel || !searchInput || !selectionTitle || !leftAccept || !leftBack || !status || !offerGrid || !requestGrid || !submitButton) {
      return;
    }

    let phase = "offer";
    let page = 0;
    const pageSize = 12;
    let ownedCards = [];
    let catalogCards = [];
    let filteredCards = [];
    const selectedOffer = {};
    const selectedRequest = {};

    function getCurrentSource() {
      return phase === "offer" ? ownedCards : catalogCards;
    }

    function getCurrentSelectedMap() {
      return phase === "offer" ? selectedOffer : selectedRequest;
    }

    function applyFilter() {
      const source = getCurrentSource();
      const query = (searchInput.value || "").trim().toLowerCase();

      filteredCards = source.filter(function (card) {
        if (!query) {
          return true;
        }

        const text = [card.player_name, card.team_name, card.rarity, String(card.id_card)].join(" ").toLowerCase();
        return text.indexOf(query) >= 0;
      });

      page = 0;
      renderLeftPage();
    }

    function renderLeftPage() {
      const selectedMap = getCurrentSelectedMap();
      const maxPage = Math.max(0, Math.ceil(filteredCards.length / pageSize) - 1);
      page = Math.max(0, Math.min(page, maxPage));
      const start = page * pageSize;
      const items = filteredCards.slice(start, start + pageSize);

      if (items.length === 0) {
        leftGrid.innerHTML = "<div class=\"col-span-4 text-center text-white/80 text-xs\">No hay cartas para mostrar.</div>";
      } else {
        leftGrid.innerHTML = items
          .map(function (card) {
            const isSelected = !!selectedMap[String(card.id_card)];
            return buildCardTile(card, isSelected, phase === "offer");
          })
          .join("");
      }

      leftPageLabel.textContent = (page + 1) + " de " + Math.max(1, maxPage + 1);
      leftPrev.disabled = page <= 0;
      leftNext.disabled = page >= maxPage;
      leftPrev.classList.toggle("opacity-50", leftPrev.disabled);
      leftNext.classList.toggle("opacity-50", leftNext.disabled);
    }

    function setPhase(nextPhase) {
      phase = nextPhase;
      selectionTitle.textContent = phase === "offer"
        ? "Selecciona las cartas de tu oferta"
        : "Selecciona las cartas que deseas recibir";
      leftAccept.textContent = phase === "offer" ? "Aceptar seleccion" : "Confirmar solicitadas";
      leftBack.classList.toggle("hidden", phase === "offer");
      status.textContent = phase === "offer"
        ? "Elige una o mas cartas que vas a intercambiar."
        : "Ahora elige las cartas que deseas recibir.";
      applyFilter();
    }

    leftGrid.addEventListener("click", function (event) {
      const button = event.target.closest(".trade-card-tile");
      if (!button) {
        return;
      }

      const cardId = button.getAttribute("data-card-id");
      if (!cardId) {
        return;
      }

      const selectedMap = getCurrentSelectedMap();
      if (selectedMap[cardId]) {
        delete selectedMap[cardId];
      } else {
        const source = getCurrentSource();
        const card = source.find(function (item) {
          return String(item.id_card) === String(cardId);
        });

        if (card) {
          selectedMap[cardId] = {
            id_card: Number(card.id_card),
            quantity: 1,
            max_quantity: Number(card.quantity || 1),
            player_name: card.player_name,
            team_name: card.team_name,
            rarity: card.rarity,
            image_url: card.image_url,
          };
        }
      }

      renderLeftPage();
      renderSelectedOfferGrid(offerGrid, selectedOffer);
      renderSelectedRequestGrid(requestGrid, selectedRequest);
    });

    offerGrid.addEventListener("click", function (event) {
      const button = event.target.closest(".trade-qty-btn");
      if (!button) {
        return;
      }

      const cardId = button.getAttribute("data-card-id");
      const op = button.getAttribute("data-op");
      if (!cardId || !selectedOffer[cardId]) {
        return;
      }

      const current = selectedOffer[cardId];
      const maxQuantity = Number(current.max_quantity || 1);
      const quantity = Number(current.quantity || 1);

      if (op === "inc") {
        current.quantity = Math.min(maxQuantity, quantity + 1);
      } else if (op === "dec") {
        current.quantity = quantity - 1;
        if (current.quantity <= 0) {
          delete selectedOffer[cardId];
        }
      }

      renderSelectedOfferGrid(offerGrid, selectedOffer);
      renderLeftPage();
    });

    leftPrev.addEventListener("click", function () {
      page -= 1;
      renderLeftPage();
    });

    leftNext.addEventListener("click", function () {
      page += 1;
      renderLeftPage();
    });

    searchInput.addEventListener("input", applyFilter);

    leftAccept.addEventListener("click", function () {
      if (phase === "offer") {
        if (Object.keys(selectedOffer).length < 1) {
          status.textContent = "Debes seleccionar al menos una carta para ofertar.";
          return;
        }
        setPhase("request");
        return;
      }

      status.textContent = Object.keys(selectedRequest).length > 0
        ? "Solicitudes confirmadas. Ya puedes publicar la oferta."
        : "Selecciona al menos una carta solicitada para continuar.";
    });

    leftBack.addEventListener("click", function () {
      setPhase("offer");
    });

    submitButton.addEventListener("click", async function () {
      const offerItems = Object.values(selectedOffer).map(function (item) {
        return { id_card: Number(item.id_card), quantity: Number(item.quantity || 1) };
      });

      const requestItems = Object.values(selectedRequest).map(function (item) {
        return { id_card: Number(item.id_card), quantity: 1 };
      });

      if (offerItems.length === 0 || requestItems.length === 0) {
        status.textContent = "Debes elegir cartas en 'Mi oferta' y en 'Estampas solicitadas'.";
        return;
      }

      setBusy(submitButton, true, "Publicando...", "Ofertar");
      status.textContent = "Publicando oferta...";

      try {
        const result = await apiRequest("/api/trades", {
          method: "POST",
          body: JSON.stringify({
            offer: offerItems,
            request: requestItems,
          }),
        });

        if (result.data.ok) {
          status.textContent = "Oferta publicada correctamente en el feed.";
          window.setTimeout(function () {
            window.location.href = "/feedOfertas";
          }, 500);
        } else {
          status.textContent = result.data.error || "No se pudo publicar la oferta.";
        }
      } catch (error) {
        status.textContent = "Error de red al publicar la oferta.";
      } finally {
        setBusy(submitButton, false, "Publicando...", "Ofertar");
      }
    });

    status.textContent = "Cargando cartas...";

    try {
      const responses = await Promise.all([
        apiRequest("/api/inventory", { method: "GET" }),
        apiRequest("/api/cards/catalog", { method: "GET" }),
      ]);

      const inventoryResult = responses[0];
      const catalogResult = responses[1];

      if (!inventoryResult.data.ok) {
        status.textContent = inventoryResult.data.error || "No se pudo cargar tu inventario.";
        return;
      }

      if (!catalogResult.data.ok) {
        status.textContent = catalogResult.data.error || "No se pudo cargar el catalogo de cartas.";
        return;
      }

      ownedCards = (inventoryResult.data.data && inventoryResult.data.data.items ? inventoryResult.data.data.items : []).filter(function (item) {
        return Number(item.quantity || 0) > 0;
      });
      catalogCards = catalogResult.data.data && catalogResult.data.data.items ? catalogResult.data.data.items : [];

      renderSelectedOfferGrid(offerGrid, selectedOffer);
      renderSelectedRequestGrid(requestGrid, selectedRequest);
      setPhase("offer");
    } catch (error) {
      status.textContent = "Error de red al cargar las cartas para el intercambio.";
    }
  }

  function renderTradeFeedCard(container, trade) {
    const senderName = trade.sender_name || "Usuario";
    const senderAvatar = trade.sender_avatar_url || "/img/image3.png";
    const offerCards = trade.cards && Array.isArray(trade.cards.offer) ? trade.cards.offer : [];
    const requestCards = trade.cards && Array.isArray(trade.cards.request) ? trade.cards.request : [];

    const offerHtml = offerCards.slice(0, 6).map(function (item) {
      return "<div class=\"w-16\"><img src=\"" + (item.image_url || "/img/image3.png") + "\" alt=\"" + (item.player_name || "Carta") + "\" class=\"w-16 h-24 rounded-md object-cover\"><p class=\"text-[10px] text-white mt-1 truncate\">" + (item.player_name || "Carta") + "</p></div>";
    }).join("");

    const requestHtml = requestCards.slice(0, 6).map(function (item) {
      return "<div class=\"w-16\"><img src=\"" + (item.image_url || "/img/image3.png") + "\" alt=\"" + (item.player_name || "Carta") + "\" class=\"w-16 h-24 rounded-md object-cover\"><p class=\"text-[10px] text-white mt-1 truncate\">" + (item.player_name || "Carta") + "</p></div>";
    }).join("");

    const isPending = String(trade.status || "") === "pending";
    const mine = !!trade.is_mine;
    let actions = "";

    if (isPending && mine) {
      actions = "<div class=\"mt-4 flex gap-2\">" +
        "<button data-action=\"cancel\" data-trade-id=\"" + trade.id_trade + "\" class=\"trade-action h-8 px-3 rounded-md bg-[#C24848] text-white text-xs font-semibold\">Cancelar</button>" +
        "</div>";
    } else if (isPending) {
      actions = "<div class=\"mt-4 flex gap-2\">" +
        "<button data-action=\"accept\" data-trade-id=\"" + trade.id_trade + "\" class=\"trade-action h-8 px-3 rounded-md bg-[#A4C351] text-[#15321A] text-xs font-semibold\">Aceptar</button>" +
        "<button data-action=\"reject\" data-trade-id=\"" + trade.id_trade + "\" class=\"trade-action h-8 px-3 rounded-md bg-[#C24848] text-white text-xs font-semibold\">Rechazar</button>" +
        "</div>";
    }

    container.innerHTML =
      "<div class=\"bg-[#006064] rounded-2xl shadow-2xl p-6 sm:p-8 md:p-10\">" +
      "<div class=\"flex items-center gap-3 text-white mb-4\">" +
      "<img src=\"" + senderAvatar + "\" alt=\"" + senderName + "\" class=\"w-8 h-8 rounded-full object-cover\">" +
      "<p class=\"text-xs font-semibold truncate\">" + senderName + "</p>" +
      "</div>" +
      "<p class=\"text-white text-xs font-semibold mb-2\">Ofrece</p>" +
      "<div class=\"flex flex-wrap gap-2 min-h-[38px]\">" + (offerHtml || "<span class=\"text-white/70 text-xs\">Sin cartas</span>") + "</div>" +
      "<p class=\"text-white text-xs font-semibold mt-4 mb-2\">Solicita</p>" +
      "<div class=\"flex flex-wrap gap-2 min-h-[38px]\">" + (requestHtml || "<span class=\"text-white/70 text-xs\">Sin cartas</span>") + "</div>" +
      actions +
      "</div>";
  }

  async function initTradeFeedPage() {
    const feedFilter = document.getElementById("tradeFeedFilter");
    const feedList = document.getElementById("tradeFeedList");
    const status = document.getElementById("tradeFeedStatus");

    if (!feedFilter || !feedList || !status) {
      return;
    }

    async function loadFeed() {
      const filter = feedFilter.value || "all";
      const query = filter === "all" ? "" : "?filter=" + encodeURIComponent(filter);
      status.textContent = filter === "completed" ? "Cargando ofertas completadas..." : "Cargando ofertas...";
      feedList.innerHTML = "";

      try {
        const result = await apiRequest("/api/trades/feed" + query, {
          method: "GET",
        });

        if (!result.data.ok) {
          status.textContent = result.data.error || "No se pudo cargar el feed.";
          return;
        }

        const trades = Array.isArray(result.data.data) ? result.data.data : [];
        if (trades.length === 0) {
          status.textContent = "No hay ofertas para mostrar.";
          return;
        }

        status.textContent = "Mostrando " + trades.length + " ofertas.";

        trades.forEach(function (trade) {
          trade.is_mine = filter === "mine";
          const wrapper = document.createElement("article");
          renderTradeFeedCard(wrapper, trade);
          feedList.appendChild(wrapper);
        });
      } catch (error) {
        status.textContent = "Error de red al cargar el feed.";
      }
    }

    feedFilter.addEventListener("change", loadFeed);

    feedList.addEventListener("click", async function (event) {
      const button = event.target.closest(".trade-action");
      if (!button) {
        return;
      }

      const action = button.getAttribute("data-action");
      const tradeId = Number(button.getAttribute("data-trade-id") || 0);
      if (!action || tradeId < 1) {
        return;
      }

      const endpoint = action === "accept"
        ? "/api/trades/accept"
        : (action === "reject" ? "/api/trades/reject" : "/api/trades/cancel");
      const idleLabel = action === "accept" ? "Aceptar" : (action === "reject" ? "Rechazar" : "Cancelar");
      setBusy(button, true, "Procesando...", idleLabel);

      try {
        const result = await apiRequest(endpoint, {
          method: "POST",
          body: JSON.stringify({ trade_id: tradeId }),
        });

        if (result.data.ok) {
          status.textContent = result.data.message || "Accion completada.";
          await loadFeed();
        } else {
          status.textContent = result.data.error || "No se pudo completar la accion.";
        }
      } catch (error) {
        status.textContent = "Error de red al ejecutar la accion.";
      } finally {
        setBusy(button, false, "Procesando...", idleLabel);
      }
    });

    const searchParams = new URLSearchParams(window.location.search);
    const queryFilter = searchParams.get("filter");
    if (queryFilter && ["all", "mine", "received", "completed"].includes(queryFilter)) {
      feedFilter.value = queryFilter;
    } else if (searchParams.get("mine") === "1") {
      feedFilter.value = "mine";
    }

    await loadFeed();
  }

  document.addEventListener("DOMContentLoaded", function () {
    initHomePage();
    initMarketPage();
    initTradeBuilderPage();
    initTradeFeedPage();
  });
})();
