((src, initCallback) => {
    const blinkLoader = document.createElement('script');
    blinkLoader.setAttribute('src', src);
    blinkLoader.onload = initCallback;
    (document.head || document.body).appendChild(blinkLoader);
})('https://ucarecdn.com/libs/blinkloader/3.x/blinkloader.min.js', () => {
    window.Blinkloader.optimize(blinkLoaderConfig);
});
