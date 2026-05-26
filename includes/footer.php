<?php /* includes/footer.php */ ?>
<nav class="bottom-nav">
  <a href="index.php"    class="nav-item <?= ($activePage=='home')    ? 'active':'' ?>" data-page="index">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
      <polyline points="9 22 9 12 15 12 15 22"/>
    </svg>
    Home
  </a>
  <a href="messages.php" class="nav-item <?= ($activePage=='messages') ? 'active':'' ?>" data-page="messages">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
    </svg>
    Messages
  </a>
  <a href="post.php" class="nav-item center-btn" data-page="post">
    <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
      <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
      <circle cx="12" cy="13" r="4"/>
    </svg>
  </a>
  <a href="archive.php"  class="nav-item <?= ($activePage=='archive')  ? 'active':'' ?>" data-page="archive">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <rect x="3" y="4" width="18" height="18" rx="2"/>
      <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
      <line x1="3" y1="10" x2="21" y2="10"/>
    </svg>
    Archive
  </a>
  <a href="profile.php"  class="nav-item <?= ($activePage=='profile')  ? 'active':'' ?>" data-page="profile">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
      <circle cx="12" cy="7" r="4"/>
    </svg>
    Profile
  </a>
</nav>
<div class="toast" id="toast"></div>
<script src="../js/main.js"></script>
</body>
</html>
