const CACHE_NAME = 'pengaduan-v1';
const assets = [
  './',
  './home.php', // atau nama file utama Anda
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://unpkg.com/lucide@latest',
  // Tambahkan path gambar atau file lain yang perlu diakses offline
];

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(assets))
  );
});

self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request).then(res => res || fetch(e.request))
  );
});