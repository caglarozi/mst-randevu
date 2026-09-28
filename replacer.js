const fs = require('fs');
let c = fs.readFileSync('mst-randevu/templates/surec.php', 'utf8');
let i = 1;
c = c.replace(/<div class="tl-empty"><\/div>/g, () => `<div class="tl-image"><img src="<?php echo MST_RANDEVU_URL; ?>assets/adim-${i++}.png" alt="Adım Karakteri" onerror="this.src='https://placehold.co/400x300/0f111a/d4af37?text=Adim+Gorseli'"></div>`);

// Add CSS for .tl-image
const css = `
    .tl-image {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        opacity: 0.9;
    }
    .tl-image img {
        max-width: 100%;
        max-height: 280px;
        object-fit: contain;
        filter: drop-shadow(0 10px 20px rgba(0,0,0,0.4));
        transition: transform 0.3s var(--ease-std);
    }
    .tl-image img:hover {
        transform: translateY(-5px) scale(1.05);
    }
`;

c = c.replace('.tl-empty { width: 50%; }', '.tl-empty { width: 50%; }' + css);
fs.writeFileSync('mst-randevu/templates/surec.php', c);
