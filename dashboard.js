/* ==== SIMPLE AUTH (demo) ==== */
const DEMO_USER = "dev1";               // change per dev in a real app
if (!localStorage.getItem('currentUser')) {
  localStorage.setItem('currentUser', DEMO_USER);
}
document.getElementById('logout')?.addEventListener('click', e => {
  e.preventDefault();
  localStorage.removeItem('currentUser');
  location.href = 'index.html';
});

/* ==== LOAD / SAVE GAMES ==== */
const STORAGE_KEY = 'gamehub_games';
function getAllGames() {
  return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
}
function saveGames(games) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(games));
}

/* ==== STATIC GAMES (your original data) ==== */
const staticGames = {
  eternal_quest: { /* … your object … */ },
  cyber_drift:   { /* … */ },
  tiny_universe:{ /* … */ },
  shadow_protocol:{ /* … */ }
  // paste the whole object you posted inside detail.js here
};

/* Merge static + user-added (static wins if duplicate) */
let allGames = { ...getAllGames(), ...staticGames };
saveGames(allGames);

/* ==== RENDER LIST ==== */
function renderList() {
  const list = document.getElementById('gamesList');
  list.innerHTML = '';
  const user = localStorage.getItem('currentUser');

  Object.entries(allGames).forEach(([id, game]) => {
    // In a real app you would store an "owner" field – for demo we allow edit of non-static
    const isStatic = staticGames.hasOwnProperty(id);
    if (isStatic) return;               // hide static games from edit list

    const div = document.createElement('div');
    div.className = 'game-item';
    div.innerHTML = `
      <div>
        <strong>${game.title}</strong> <small>(${game.category})</small>
      </div>
      <div class="actions">
        <button class="btn" data-edit="${id}">Modifier</button>
        <button class="btn danger" data-del="${id}">Supprimer</button>
      </div>
    `;
    list.appendChild(div);
  });

  // edit / delete handlers
  list.querySelectorAll('[data-edit]').forEach(btn => {
    btn.addEventListener('click', () => editGame(btn.dataset.edit));
  });
  list.querySelectorAll('[data-del]').forEach(btn => {
    btn.addEventListener('click', () => deleteGame(btn.dataset.del));
  });
}

/* ==== ADD NEW GAME ==== */
document.getElementById('addGameForm').addEventListener('submit', e => {
  e.preventDefault();
  const id = document.getElementById('gameId').value.trim().replace(/\s/g,'_').toLowerCase();
  if (allGames[id]) { alert('Cet ID existe déjà'); return; }

  const newGame = {
    title:       document.getElementById('title').value,
    trailer:     document.getElementById('trailer').value,
    description: document.getElementById('description').value,
    date:        document.getElementById('date').value,
    category:    document.getElementById('category').value,
    devName:     document.getElementById('devName').value,
    devStudio:   document.getElementById('devStudio').value,
    devBio:      document.getElementById('devBio').value,
    devLink:     document.getElementById('devLink').value
  };

  allGames[id] = newGame;
  saveGames(allGames);
  e.target.reset();
  renderList();
});

/* ==== EDIT (populate form) ==== */
function editGame(id) {
  const g = allGames[id];
  document.getElementById('gameId').value = id;
  document.getElementById('title').value = g.title;
  document.getElementById('trailer').value = g.trailer;
  document.getElementById('description').value = g.description;
  document.getElementById('date').value = g.date;
  document.getElementById('category').value = g.category;
  document.getElementById('devName').value = g.devName;
  document.getElementById('devStudio').value = g.devStudio;
  document.getElementById('devBio').value = g.devBio;
  document.getElementById('devLink').value = g.devLink;

  // change button to UPDATE
  const submitBtn = document.querySelector('#addGameForm button[type=submit]');
  submitBtn.textContent = 'Mettre à jour';
  submitBtn.dataset.update = id;
}

/* ==== UPDATE (when form is submitted after edit) ==== */
document.querySelector('#addGameForm button[type=submit]').addEventListener('click', e => {
  if (e.target.dataset.update) {
    const id = e.target.dataset.update;
    const updated = {
      title:       document.getElementById('title').value,
      trailer:     document.getElementById('trailer').value,
      description: document.getElementById('description').value,
      date:        document.getElementById('date').value,
      category:    document.getElementById('category').value,
      devName:     document.getElementById('devName').value,
      devStudio:   document.getElementById('devStudio').value,
      devBio:      document.getElementById('devBio').value,
      devLink:     document.getElementById('devLink').value
    };
    allGames[id] = updated;
    saveGames(allGames);
    e.target.textContent = 'Ajouter le jeu';
    delete e.target.dataset.update;
    document.getElementById('addGameForm').reset();
    renderList();
  }
});

/* ==== DELETE ==== */
function deleteGame(id) {
  if (confirm(`Supprimer "${allGames[id].title}" ?`)) {
    delete allGames[id];
    saveGames(allGames);
    renderList();
  }
}

/* ==== INITIAL RENDER ==== */
renderList();