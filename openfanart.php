<?php
/**
 * Project: OpenFanart
 * Author: QWERTZexe
 * Repository: https://github.com/QWERTZexe/OpenFanart/
 *
 * Copyright (c) 2026 QWERTZexe
 *
 * This file is part of the OpenFanart project.
 * See repository for license information.
 */

$artworks = json_decode(file_get_contents('art.json'), true);
$artists = array_unique(array_map(fn($a) => $a['artistName'], $artworks));
sort($artists);

$artParam = $_GET['art'] ?? null;
$vParam = isset($_GET['v']) ? (int)$_GET['v'] : 0;

$selectedArt = null;
$selectedImage = null;
$embedTitle = 'Fanart Gallery';
$embedDescription = 'Browse fanart from multiple artists.';
$embedImage = '';
$embedUrl = '';
$embedType = 'website';

function absolute_url(string $path = ''): string {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    if ($path && preg_match('~^https?://~i', $path)) {
        return $path;
    }

    $base = $scheme . '://' . $host;

    if (!$path) {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        return $base . $requestUri;
    }

    return $base . '/' . ltrim($path, '/');
}

if ($artParam !== null && isset($artworks[$artParam])) {
    $selectedArt = $artworks[$artParam];
    $versions = $selectedArt['versions'] ?? [$selectedArt['path']];

    if (!isset($versions[$vParam])) {
        $vParam = 0;
    }

    $selectedImage = $versions[$vParam] ?? $selectedArt['path'];
    $embedTitle = $selectedArt['name'] . ' — ' . $selectedArt['artistName'];
    $embedDescription = 'View this artwork in the Fanart Gallery.';
    $embedImage = absolute_url($selectedImage);
    $embedType = 'article';
}

$embedUrl = absolute_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!--
    OpenFanart — Fanart Gallery Frontend
    Copyright (c) 2026 QWERTZexe
    https://github.com/QWERTZexe/OpenFanart/
  -->

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title><?php echo htmlspecialchars($embedTitle); ?></title>

  <meta property="og:type" content="<?php echo htmlspecialchars($embedType); ?>">
  <meta property="og:site_name" content="Fanart Gallery">
  <meta property="og:title" content="<?php echo htmlspecialchars($embedTitle); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($embedDescription); ?>">
  <meta property="og:url" content="<?php echo htmlspecialchars($embedUrl); ?>">

  <?php if (!empty($embedImage)): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($embedImage); ?>">
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($embedImage); ?>">
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($embedTitle); ?>">
  <?php endif; ?>

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($embedTitle); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($embedDescription); ?>">
  <?php if (!empty($embedImage)): ?>
    <meta name="twitter:image" content="<?php echo htmlspecialchars($embedImage); ?>">
  <?php endif; ?>

  <style>
    html, body { height: 100%; margin: 0; overscroll-behavior: none; }
    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(-10deg, #000000, #550000);
      background-attachment: fixed;
      background-repeat: no-repeat;
      background-size: cover;
      color: #eee;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .controls {
      position: sticky; top: 0; z-index: 100;
      padding: 16px 40px;
      background: rgba(20, 0, 0, 0.9);
      backdrop-filter: blur(8px);
      display: flex; flex-wrap: wrap; gap: 16px;
      align-items: center; justify-content: space-between;
      box-shadow: 0 2px 10px rgba(0,0,0,0.4);
    }

    .controls input {
      padding: 10px 16px; border-radius: 10px;
      border: 1px solid #5a1111; font-size: 16px;
      background: #300000; color: #fff;
      flex: 1; max-width: 240px; outline: none;
      transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .controls input::placeholder { color: #d9aaaa; }
    .controls input:focus {
      border-color: #ff6b6b;
      box-shadow: 0 0 0 3px rgba(255,85,85,.18);
      background: #3a0000;
    }

    .counts {
      font-size: 18px; font-weight: 600; color: #ffcccc;
      background: #2c0000; padding: 8px 16px; border-radius: 10px;
      box-shadow: inset 0 0 4px rgba(255,0,0,.4);
      flex: 1; text-align: center; max-width: 220px; user-select: none;
    }

    .custom-select { position: relative; flex: 1; max-width: 240px; }
    .custom-select-trigger {
      width: 100%; padding: 10px 16px; border-radius: 10px;
      border: 1px solid #5a1111; font-size: 16px;
      background: #300000; color: #fff; display: flex;
      align-items: center; justify-content: space-between; gap: 12px;
      cursor: pointer; outline: none;
      transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }
    .custom-select-trigger:hover { background: #3a0000; }
    .custom-select-trigger:focus, .custom-select.open .custom-select-trigger {
      border-color: #ff6b6b;
      box-shadow: 0 0 0 3px rgba(255,85,85,.18);
      background: #3a0000;
    }

    .custom-select-arrow { font-size: 14px; color: #ffb3b3; transition: transform .2s ease; }
    .custom-select.open .custom-select-arrow { transform: rotate(180deg); }

    .custom-select-menu {
      position: absolute; top: calc(100% + 8px); left: 0; right: 0;
      background: #220000; border: 1px solid #5a1111; border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,.45); overflow: hidden;
      z-index: 300; display: none; max-height: 280px; overflow-y: auto;
    }
    .custom-select.open .custom-select-menu { display: block; }

    .custom-select-option {
      padding: 12px 14px; color: #ffe1e1; cursor: pointer;
      transition: background .15s ease, color .15s ease;
    }
    .custom-select-option:hover { background: #4a0000; color: #fff; }
    .custom-select-option.selected { background: #5a1111; color: #fff; font-weight: 600; }

    .custom-select-menu::-webkit-scrollbar { width: 10px; }
    .custom-select-menu::-webkit-scrollbar-track { background: #1a0000; }
    .custom-select-menu::-webkit-scrollbar-thumb {
      background: #6e1b1b; border-radius: 999px; border: 2px solid #1a0000;
    }

    .gallery {
      column-count: 3; column-gap: 24px; max-width: 1200px;
      margin: 40px auto; padding: 0 12px; width: 100%;
    }
    @media (max-width: 900px){ .gallery { column-count: 3; } }
    @media (max-width: 600px){ .gallery { column-count: 2; } .controls { padding: 16px; } }
    @media (max-width: 400px){ .gallery { column-count: 1; } }

    .card {
      background: #1a0000; border-radius: 16px;
      box-shadow: 0 5px 15px rgba(0,0,0,.5);
      margin-bottom: 24px; cursor: pointer; display: inline-block;
      width: 100%; transform: scale(1);
      transition: opacity .3s ease, transform .3s ease;
      break-inside: avoid; color: #fdd; padding-bottom: 12px;
    }
    .card.hidden {
      opacity: 0; transform: scale(.95); pointer-events: none;
      height: 0; margin: 0; padding: 0;
    }
    .card:hover { transform: scale(1.02); }

    .card img.art {
      width: 100%; display: block; border-bottom: 2px solid maroon;
      border-radius: 16px 16px 0 0;
    }

    .image-wrapper { position: relative; }
    .multi-icon {
      position: absolute; top: 8px; right: 8px;
      background: rgba(60,0,0,.85); border-radius: 6px; padding: 6px;
      font-size: 28px; line-height: 1; color: #fff;
      box-shadow: 0 0 5px rgba(0,0,0,.6); z-index: 10;
      width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;
    }

    .artwork-title {
      font-weight: bold; font-size: 18px;
      margin: 12px 16px 6px; color: #ffbbbb; text-align: center;
    }

    .artist { display: flex; align-items: center; gap: 12px; padding: 0 16px; }
    .artist img {
      border-radius: 50%; width: 42px; height: 42px; object-fit: cover;
      border: 2px solid maroon;
    }
    .artist-name { font-size: 16px; font-weight: bold; color: #fdd; }

    .modal {
      position: fixed; inset: 0; background: rgba(0,0,0,.95);
      display: none; flex-direction: column; justify-content: center; align-items: center;
      z-index: 999; padding: 20px;
    }
    .modal.active { display: flex; }

    .modal img {
      max-width: 90%; max-height: 75vh; border-radius: 12px;
      box-shadow: 0 0 40px rgba(255,0,0,.3); transition: all .3s ease;
    }

    .close {
      position: absolute; top: 20px; right: 30px; font-size: 32px;
      color: #fff; cursor: pointer; user-select: none;
    }

    .bottom-bar {
      background: #400000e0; border-radius: 12px; padding: 12px 20px;
      margin-top: 20px; display: flex; align-items: center; justify-content: space-between;
      width: 100%; max-width: 90%; color: #fff; gap: 16px; flex-wrap: wrap;
    }
    .bottom-bar .left-group { display: flex; align-items: center; gap: 16px; }
    .bottom-bar .right-group { display: flex; align-items: center; gap: 12px; margin-left: auto; }

    .dots { display: flex; gap: 10px; align-items: center; }
    .dot { width: 14px; height: 14px; background: #777; border-radius: 50%; transition: .3s; cursor: pointer; }
    .dot.active { background: #ff5555; transform: scale(1.3); }

    .arrow {
      position: absolute; top: 50%; transform: translateY(-50%);
      font-size: 40px; color: #fff; cursor: pointer; padding: 10px;
      user-select: none; background: rgba(0,0,0,.4); border-radius: 50%;
    }
    .arrow.left { left: 30px; }
    .arrow.right { right: 30px; }

    .hidden { display: none !important; }

    #shareBtn {
      position: relative; background: none; border: none; color: #fff;
      font-size: 18px; cursor: pointer; padding: 0;
    }
    #shareBtn:hover::after {
      content: "Copy share link"; position: absolute; background: #000; color: #fff;
      font-size: 12px; padding: 6px 10px; border-radius: 6px; top: -32px; right: 0;
      white-space: nowrap; pointer-events: none; opacity: .9; z-index: 1000;
    }

    footer {
      margin-top: auto; text-align: center; padding: 14px 10px; font-size: 12px;
      color: #ffb3b3; background: rgba(0,0,0,.35); backdrop-filter: blur(4px);
    }
    footer a { color:#ff8080; text-decoration:none; }
  </style>
</head>
<body>

<div class="controls">
  <input type="text" id="searchInput" placeholder="Search by title..." />
  <div class="counts" id="counts">🖼️: 0 🧑‍🎨: 0</div>

  <div class="custom-select" id="artistDropdown">
    <button type="button" class="custom-select-trigger" id="artistTrigger">
      <span id="artistSelectedLabel">All Artists</span>
      <span class="custom-select-arrow">▾</span>
    </button>

    <div class="custom-select-menu" id="artistMenu">
      <div class="custom-select-option selected" data-value="" data-label="All Artists">All Artists</div>
      <?php foreach ($artists as $artist): ?>
        <div class="custom-select-option"
          data-value="<?php echo htmlspecialchars(strtolower($artist), ENT_QUOTES); ?>"
          data-label="<?php echo htmlspecialchars($artist, ENT_QUOTES); ?>">
          <?php echo htmlspecialchars($artist); ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="gallery" id="gallery">
  <?php foreach ($artworks as $id => $art): ?>
    <div class="card"
      data-name="<?php echo htmlspecialchars(strtolower($art['name']), ENT_QUOTES); ?>"
      data-artist="<?php echo htmlspecialchars(strtolower($art['artistName']), ENT_QUOTES); ?>"
      onclick="openModal('<?php echo htmlspecialchars($id, ENT_QUOTES); ?>')">
      <div class="image-wrapper">
        <img class="art" src="<?php echo htmlspecialchars($art['path']); ?>" alt="<?php echo htmlspecialchars($art['name']); ?>" />
        <?php if (isset($art['versions']) && count($art['versions']) > 1): ?>
          <div class="multi-icon" title="Multiple versions"><i class="fas fa-images"></i></div>
        <?php endif; ?>
      </div>
      <div class="artwork-title"><?php echo htmlspecialchars($art['name']); ?></div>
      <div class="artist">
        <img src="<?php echo htmlspecialchars($art['artistPicture']); ?>" alt="<?php echo htmlspecialchars($art['artistName']); ?>" />
        <div class="artist-name"><?php echo htmlspecialchars($art['artistName']); ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="modal" id="artModal">
  <div class="close" onclick="closeModal()">×</div>
  <div class="arrow left" onclick="changeVersion(-1)">‹</div>
  <div class="arrow right" onclick="changeVersion(1)">›</div>
  <img id="modalImage" src="" alt="" />
  <div class="bottom-bar">
    <div class="left-group">
      <div class="artist">
        <img id="modalArtistPic" src="" alt="" />
        <div class="artist-name" id="modalArtist">Artist</div>
      </div>
    </div>
    <div class="right-group">
      <button id="shareBtn" onclick="copyShareLink()" title="Copy share link">🔗 Share</button>
      <div class="dots" id="versionDots"></div>
    </div>
  </div>
</div>

<footer>
  © 2026 OpenFanart - Made by QWERTZexe ·
  <a href="https://github.com/QWERTZexe/OpenFanart/" target="_blank">GitHub Repository</a>
</footer>

<script>
  const artworks = <?php echo json_encode($artworks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
  let currentVersions = [];
  let currentIndex = 0;
  let currentArtId = null;
  let selectedArtistValue = '';

  function openModal(id) {
    currentArtId = id;
    const art = artworks[id];
    currentVersions = art.versions || [art.path];
    currentIndex = 0;

    const img = document.getElementById('modalImage');
    const artist = document.getElementById('modalArtist');
    const pic = document.getElementById('modalArtistPic');
    const dots = document.getElementById('versionDots');

    img.src = currentVersions[0];
    img.alt = art.name;
    artist.textContent = art.artistName;
    pic.src = art.artistPicture;
    pic.alt = art.artistName;

    dots.innerHTML = '';
    currentVersions.forEach((_, i) => {
      const d = document.createElement('div');
      d.className = 'dot' + (i === 0 ? ' active' : '');
      d.onclick = () => {
        currentIndex = i;
        img.src = currentVersions[i];
        updateDots();
        syncUrl();
      };
      dots.appendChild(d);
    });

    document.getElementById('artModal').classList.add('active');
    syncUrl();
  }

  function closeModal() {
    document.getElementById('artModal').classList.remove('active');
    const url = new URL(window.location.href);
    url.searchParams.delete('art');
    url.searchParams.delete('v');
    history.replaceState({}, '', url);
  }

  function changeVersion(dir) {
    if (currentVersions.length <= 1) return;
    currentIndex = (currentIndex + dir + currentVersions.length) % currentVersions.length;
    document.getElementById('modalImage').src = currentVersions[currentIndex];
    updateDots();
    syncUrl();
  }

  function updateDots() {
    document.querySelectorAll('#versionDots .dot').forEach((dot, i) => {
      dot.classList.toggle('active', i === currentIndex);
    });
  }

  function syncUrl() {
    if (!currentArtId) return;
    const url = new URL(window.location.href);
    url.searchParams.set('art', currentArtId);
    url.searchParams.set('v', currentIndex);
    history.replaceState({}, '', url);
  }

  function copyShareLink() {
    if (!currentArtId) return;
    const url = new URL(window.location.href);
    url.searchParams.set('art', currentArtId);
    url.searchParams.set('v', currentIndex);
    navigator.clipboard.writeText(url.toString())
      .then(() => alert('Share link copied!'))
      .catch(() => alert('Failed to copy share link'));
  }

  document.addEventListener('keydown', e => {
    const modalOpen = document.getElementById('artModal').classList.contains('active');
    if (e.key === 'Escape') closeModal();
    if (modalOpen && e.key === 'ArrowLeft') changeVersion(-1);
    if (modalOpen && e.key === 'ArrowRight') changeVersion(1);
  });

  const searchInput = document.getElementById('searchInput');
  const gallery = document.getElementById('gallery');
  const counts = document.getElementById('counts');

  const artistDropdown = document.getElementById('artistDropdown');
  const artistTrigger = document.getElementById('artistTrigger');
  const artistMenu = document.getElementById('artistMenu');
  const artistSelectedLabel = document.getElementById('artistSelectedLabel');
  const artistOptions = document.querySelectorAll('.custom-select-option');

  artistTrigger.addEventListener('click', () => artistDropdown.classList.toggle('open'));

  artistOptions.forEach(option => {
    option.addEventListener('click', () => {
      selectedArtistValue = (option.dataset.value || '').trim().toLowerCase();
      artistSelectedLabel.textContent = option.dataset.label || option.textContent.trim();
      artistOptions.forEach(opt => opt.classList.remove('selected'));
      option.classList.add('selected');
      artistDropdown.classList.remove('open');
      updateGallery();
    });
  });

  document.addEventListener('click', e => {
    if (!artistDropdown.contains(e.target)) artistDropdown.classList.remove('open');
  });

  function updateGallery() {
    const searchTerm = searchInput.value.trim().toLowerCase();
    const artistTerm = selectedArtistValue.trim().toLowerCase();

    let visibleCards = 0;
    let visibleArtists = new Set();

    [...gallery.children].forEach(card => {
      const name = card.getAttribute('data-name') || '';
      const artist = card.getAttribute('data-artist') || '';

      const matchesSearch = !searchTerm || name.includes(searchTerm);
      const matchesArtist = !artistTerm || artist === artistTerm;

      if (matchesSearch && matchesArtist) {
        card.classList.remove('hidden');
        visibleCards++;
        visibleArtists.add(artist);
      } else {
        card.classList.add('hidden');
      }
    });

    counts.textContent = `🖼️: ${visibleCards} 🧑‍🎨: ${visibleArtists.size}`;
  }

  searchInput.addEventListener('input', updateGallery);
  updateGallery();

  const urlParams = new URLSearchParams(window.location.search);
  const artFromUrl = urlParams.get('art');
  const versionFromUrl = parseInt(urlParams.get('v'), 10);

  if (artFromUrl && artworks[artFromUrl]) {
    openModal(artFromUrl);
    const versions = artworks[artFromUrl].versions || [artworks[artFromUrl].path];
    if (!isNaN(versionFromUrl) && versions[versionFromUrl]) {
      currentIndex = versionFromUrl;
      document.getElementById('modalImage').src = versions[versionFromUrl];
      updateDots();
      syncUrl();
    }
  }
</script>
</body>
</html>
