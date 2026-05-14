<?php
// Sadece POST ile gelen metni sese çevirip MP3 döndürür
// Görünen hiçbir şey yok, direkt ses dosyası!

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['text'] ?? '';
    
    if (empty($text)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Metin girilmedi']);
        exit;
    }

    // Google TTS URL'si (ücretsiz, limitsiz)
    $url = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=tr&q=' . urlencode($text);
    
    // Ses dosyasını al
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: audio/mpeg',
            'Accept-Language: tr-TR,tr;q=0.9'
        ]
    ]);
    
    $audio = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $audio) {
        // Direkt ses olarak döndür
        header('Content-Type: audio/mpeg');
        header('Content-Length: ' . strlen($audio));
        header('Cache-Control: no-cache');
        echo $audio;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Ses oluşturulamadı']);
    }
    exit;
}

// GET isteği gelirse basit form göster
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ses API</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #111; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: Arial; }
        form { background: #1a1a1a; padding: 30px; border-radius: 15px; width: 90%; max-width: 500px; }
        textarea { width: 100%; height: 120px; padding: 15px; background: #222; color: #fff; border: 1px solid #333; border-radius: 10px; font-size: 16px; resize: vertical; }
        button { width: 100%; padding: 15px; background: #00d4aa; color: #000; border: none; border-radius: 10px; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 15px; }
        button:hover { background: #00b892; }
        h3 { color: #fff; margin-bottom: 15px; text-align: center; }
        audio { width: 100%; margin-top: 15px; display: none; }
    </style>
</head>
<body>
    <form id="form">
        <h3>🎙️ Metin Gir, Ses Olarak Dinle</h3>
        <textarea id="text" placeholder="Buraya metin yaz..."></textarea>
        <button type="submit">🔊 Sese Çevir</button>
        <audio id="audio" controls></audio>
    </form>

    <script>
        document.getElementById('form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = document.getElementById('text').value;
            if (!text) return;
            
            const formData = new FormData();
            formData.append('text', text);
            
            const response = await fetch('', { method: 'POST', body: formData });
            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            
            const audio = document.getElementById('audio');
            audio.src = url;
            audio.style.display = 'block';
            audio.play();
        });
    </script>
</body>
</html>
