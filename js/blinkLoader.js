((src, cb) => {
    var s = document.createElement('script'); s.setAttribute('src', src);
    s.onload = cb; (document.head || document.body).appendChild(s);
})('https://ucarecdn.com/libs/blinkloader/3.x/blinkloader.min.js', function() {
    window.Blinkloader.optimize({
        pubkey: UC_PUBLIC_KEY,
        fadeIn: true,
    });
})
