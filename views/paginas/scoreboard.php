<main class="flex-1 flex flex-col px-4 sm:px-6 md:px-10 lg:px-16 py-4 sm:py-6">
    <h1 class="text-white font-extrabold tracking-wide text-3xl sm:text-4xl md:text-5xl lg:text-6xl mt-2 sm:mt-4 mb-6 sm:mb-8 md:mb-10">MUNDIAL 2026</h1>

    <div class="w-full max-w-4xl mx-auto">
        <!-- Standings Section -->
        <div class="mb-8">
            <h2 class="text-white text-2xl font-bold mb-4 border-b border-white/20 pb-3">Clasificaciones</h2>
            <div id="standingsContainer" class="space-y-2 max-h-96 overflow-y-auto">
                <p class="text-white/70 text-center py-8">Cargando clasificaciones...</p>
            </div>
        </div>

        <!-- Matches Section -->
        <div>
            <h2 class="text-white text-2xl font-bold mb-4 border-b border-white/20 pb-3">Partidos</h2>
            <div id="matchesContainer" class="space-y-3">
                <p class="text-white/70 text-center py-8">Cargando partidos...</p>
            </div>
            <div id="matchesPagination" class="mt-3 flex items-center justify-center"></div>
        </div>

        <!-- Error Display -->
        <div id="scoreboardError" class="hidden mt-4 p-4 bg-red-500/20 border border-red-500/50 rounded-lg">
            <p id="scoreboardErrorText" class="text-red-300"></p>
        </div>
    </div>
</main>

<style>
    @keyframes pack-spin {
        from {
            transform: rotateY(0deg) rotateX(0deg);
        }
        to {
            transform: rotateY(360deg) rotateX(0deg);
        }
    }

    #standingsContainer::-webkit-scrollbar,
    #matchesContainer::-webkit-scrollbar {
        width: 6px;
    }

    #standingsContainer::-webkit-scrollbar-track,
    #matchesContainer::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
    }

    #standingsContainer::-webkit-scrollbar-thumb,
    #matchesContainer::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
    }

    #standingsContainer::-webkit-scrollbar-thumb:hover,
    #matchesContainer::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
</style>

<script>
let __allMatches = [];
let __currentPage = 1;
const __pageSize = 5;

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('/api/external/scoreboard');
        const result = await response.json();

        if (!result.ok) {
            throw new Error(result.error || 'Error desconocido');
        }

        const { matches, standings } = result.data;
        __allMatches = matches || [];
        renderStandings(standings);
        renderMatchesPage(1);
    } catch (error) {
        console.error('Error loading scoreboard:', error);
        showError(error.message || 'Error de red al cargar el scoreboard');
    }
});

function renderMatchesPage(page) {
    const total = __allMatches.length;
    const maxPage = Math.max(1, Math.ceil(total / __pageSize));
    const p = Math.min(Math.max(1, page), maxPage);
    __currentPage = p;
    const start = (p - 1) * __pageSize;
    const slice = __allMatches.slice(start, start + __pageSize);
    renderMatches(slice);
    renderPagination(total, p, maxPage);
}

function renderPagination(totalItems, currentPage, maxPage) {
    const container = document.getElementById('matchesPagination');
    if (!container) return;

    if (totalItems <= __pageSize) {
        container.innerHTML = '';
        return;
    }

    const prevDisabled = currentPage <= 1 ? 'opacity-50 pointer-events-none' : '';
    const nextDisabled = currentPage >= maxPage ? 'opacity-50 pointer-events-none' : '';

    container.innerHTML = `
        <div class="flex items-center gap-3">
            <button id="pagPrev" class="px-3 py-1 bg-white/5 rounded ${prevDisabled}">Anterior</button>
            <span class="text-white/70 text-sm">Página ${currentPage} de ${maxPage}</span>
            <button id="pagNext" class="px-3 py-1 bg-white/5 rounded ${nextDisabled}">Siguiente</button>
        </div>
    `;

    document.getElementById('pagPrev').addEventListener('click', () => renderMatchesPage(currentPage - 1));
    document.getElementById('pagNext').addEventListener('click', () => renderMatchesPage(currentPage + 1));
}

function showError(message) {
    const errorDiv = document.getElementById('scoreboardError');
    const errorText = document.getElementById('scoreboardErrorText');
    errorText.textContent = message;
    errorDiv.classList.remove('hidden');
}

function renderStandings(standings) {
    const container = document.getElementById('standingsContainer');
    
    if (!standings || standings.length === 0) {
        container.innerHTML = '<p class="text-white/70 text-center py-4">Sin clasificaciones disponibles</p>';
        return;
    }

    let html = '';
    
    standings.forEach(standing => {
        if (!standing.table) return;
        
        // Compute a friendly group/title label
        let groupTitle = 'Grupo';
        if (standing.group) {
            let g = String(standing.group).replace(/^GROUP[_\s]?/i, '').replace(/^Group\s*/i, '').trim();
            groupTitle = `Grupo ${g}`;
        } else if (standing.stage) {
            groupTitle = String(standing.stage).replace(/_/g, ' ');
        }

        html += `<div class="mb-4 p-3 bg-white/5 rounded-lg border border-white/10">
            <h3 class="text-white font-bold mb-2 text-sm">${groupTitle}</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-white/90">
                    <thead>
                        <tr class="border-b border-white/20">
                            <th class="text-left px-2 py-1">Pos</th>
                            <th class="text-left px-2 py-1">Equipo</th>
                            <th class="text-center px-2 py-1">PJ</th>
                            <th class="text-center px-2 py-1">G</th>
                            <th class="text-center px-2 py-1">E</th>
                            <th class="text-center px-2 py-1">P</th>
                            <th class="text-center px-2 py-1">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${standing.table.map((team, idx) => `
                            <tr class="border-b border-white/5 ${idx < 2 ? 'bg-green-500/10' : ''}">
                                <td class="px-2 py-1 font-bold">${idx + 1}</td>
                                <td class="px-2 py-1">${team.team?.name || '-'}</td>
                                <td class="text-center">${team.playedGames || 0}</td>
                                <td class="text-center">${team.won || 0}</td>
                                <td class="text-center">${team.draw || 0}</td>
                                <td class="text-center">${team.lost || 0}</td>
                                <td class="text-center font-bold">${team.points || 0}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>`;
    });

    container.innerHTML = html || '<p class="text-white/70 text-center py-4">Sin datos disponibles</p>';
}

function renderMatches(matches) {
    const container = document.getElementById('matchesContainer');
    
    if (!matches || matches.length === 0) {
        container.innerHTML = '<p class="text-white/70 text-center py-8">Sin partidos disponibles</p>';
        return;
    }

    let html = matches.map(match => {
        const homeTeam = match.homeTeam?.name || 'Por confirmar';
        const awayTeam = match.awayTeam?.name || 'Por confirmar';
        const homeScore = match.score?.fullTime?.home ?? '-';
        const awayScore = match.score?.fullTime?.away ?? '-';
        const status = match.status || 'TIMED';
        const utcDate = match.utcDate ? new Date(match.utcDate) : null;
        const dateStr = utcDate ? utcDate.toLocaleDateString('es-ES', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }) : 'Fecha no disponible';

        let statusColor = 'text-blue-400';
        let statusBg = 'bg-blue-500/20';
        
        if (status === 'LIVE') {
            statusColor = 'text-red-400';
            statusBg = 'bg-red-500/20';
        } else if (status === 'FINISHED') {
            statusColor = 'text-green-400';
            statusBg = 'bg-green-500/20';
        } else if (status === 'TIMED' || status === 'SCHEDULED') {
            statusColor = 'text-blue-400';
            statusBg = 'bg-blue-500/20';
        }

        return `
            <div class="p-4 bg-white/5 rounded-lg border border-white/10 hover:bg-white/10 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold ${statusColor}">● ${status}</span>
                    <span class="text-xs text-white/60">${dateStr}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex-1 text-right">
                        <p class="text-white font-bold text-sm">${homeTeam}</p>
                    </div>
                    <div class="px-4 text-center">
                        <p class="text-white font-extrabold text-2xl">${homeScore} - ${awayScore}</p>
                    </div>
                    <div class="flex-1">
                        <p class="text-white font-bold text-sm">${awayTeam}</p>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;
}
</script>
