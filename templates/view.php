<div class="memorial-page">
    <div class="memorial-card">
        <div class="memorial-cross">&#10013;</div>

        <p class="memorial-subtitle">In Loving Memory of</p>

        <h1 class="memorial-name"><?= htmlspecialchars($brochure['deceased_name']) ?></h1>

        <?php if ($brochure['title']): ?>
        <p class="memorial-title"><?= htmlspecialchars($brochure['title']) ?></p>
        <?php endif; ?>

        <div class="memorial-divider"></div>

        <div class="memorial-location">
            <svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" style="vertical-align: -3px; opacity:.7">
                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
            </svg>
            <?= htmlspecialchars($brochure['funeral_location']) ?>
        </div>

        <a href="/brochure/<?= htmlspecialchars($brochure['slug']) ?>/pdf"
           class="btn btn-dark btn-lg memorial-btn" target="_blank">
            <svg width="20" height="20" viewBox="0 0 16 16" fill="currentColor" style="vertical-align:-3px">
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a6.12 6.12 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029z"/>
            </svg>
            View Brochure
        </a>
    </div>
</div>
