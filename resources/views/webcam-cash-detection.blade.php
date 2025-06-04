<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deteksi Keaslian Uang (Webcam)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.20/lodash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/async/3.2.0/async.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/inferencejs"></script>
    <style>
        html, body, video, canvas { width: 100%; height: 100%; margin: 0; padding: 0; }
        video, canvas { position: fixed; top: 0; left: 0; }
        body { background-color: black; color: white; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; }
        body video { transition: filter 250ms linear; }
        body.loading video { filter: grayscale(1) brightness(0.25); }
        body.loading:before { content: "Loading Model..."; color: white; text-align: center; width: 100%; position: absolute; top: 20px; font-size: 3em; font-weight: bold; z-index: 100; }
        body:after { content: ""; position: fixed; bottom: 20px; right: 20px; width: 350px; height: 150px; z-index: 1; background-image: url("https://uploads-ssl.webflow.com/5eca8e43c4dfff837f0f6392/5ecad58e650fb5ec53e8b811_roboflow_assets_logo_white.png"); background-size: contain; background-repeat: no-repeat; background-position: bottom right; }
        #fps { position: fixed; bottom: 10px; left: 10px; }
        #fps:empty { display: none; }
        #fps:after { content: " fps"; }
    </style>
</head>
<body class="loading">
    <video id="video" autoplay muted playsinline></video>
    <div id="fps"></div>
    <a href="/admin/point-of-sale" style="position:fixed;top:20px;left:20px;z-index:200;" class="px-4 py-2 bg-gray-400 text-white rounded">Kembali ke POS</a>
    <script>
    $(function () {
        const { InferenceEngine, CVImage } = inferencejs;
        const inferEngine = new InferenceEngine();
        const video = $("video")[0];
        var workerId;
        var cameraMode = "environment";
        const startVideoStreamPromise = navigator.mediaDevices
            .getUserMedia({ audio: false, video: { facingMode: cameraMode } })
            .then(function (stream) {
                return new Promise(function (resolve) {
                    video.srcObject = stream;
                    video.onloadeddata = function () {
                        video.play();
                        resolve();
                    };
                });
            });
        const loadModelPromise = new Promise(function (resolve, reject) {
            inferEngine
                .startWorker("deteksi-uang-palsu-skqya", "1", "rf_Z6uGJcKI23ZMxBsBRXhnoXiAiGG3")
                .then(function (id) {
                    workerId = id;
                    resolve();
                })
                .catch(reject);
        });
        Promise.all([startVideoStreamPromise, loadModelPromise]).then(function () {
            $("body").removeClass("loading");
            resizeCanvas();
            detectFrame();
        });
        var canvas, ctx;
        const font = "16px sans-serif";
        function videoDimensions(video) {
            var videoRatio = video.videoWidth / video.videoHeight;
            var width = video.offsetWidth, height = video.offsetHeight;
            var elementRatio = width / height;
            if (elementRatio > videoRatio) {
                width = height * videoRatio;
            } else {
                height = width / videoRatio;
            }
            return { width: width, height: height };
        }
        $(window).resize(function () { resizeCanvas(); });
        const resizeCanvas = function () {
            $("canvas").remove();
            canvas = $("<canvas/>");
            ctx = canvas[0].getContext("2d");
            var dimensions = videoDimensions(video);
            canvas[0].width = video.videoWidth;
            canvas[0].height = video.videoHeight;
            canvas.css({ width: dimensions.width, height: dimensions.height, left: ($(window).width() - dimensions.width) / 2, top: ($(window).height() - dimensions.height) / 2 });
            $("body").append(canvas);
        };
        const renderPredictions = function (predictions) {
            var scale = 1;
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            predictions.forEach(function (prediction) {
                const x = prediction.bbox.x;
                const y = prediction.bbox.y;
                const width = prediction.bbox.width;
                const height = prediction.bbox.height;
                ctx.strokeStyle = prediction.color;
                ctx.lineWidth = 4;
                ctx.strokeRect((x - width / 2) / scale, (y - height / 2) / scale, width / scale, height / scale);
                ctx.fillStyle = prediction.color;
                const textWidth = ctx.measureText(prediction.class).width;
                const textHeight = parseInt(font, 10);
                ctx.fillRect((x - width / 2) / scale, (y - height / 2) / scale, textWidth + 8, textHeight + 4);
            });
            predictions.forEach(function (prediction) {
                const x = prediction.bbox.x;
                const y = prediction.bbox.y;
                const width = prediction.bbox.width;
                const height = prediction.bbox.height;
                ctx.font = font;
                ctx.textBaseline = "top";
                ctx.fillStyle = "#000000";
                ctx.fillText(prediction.class, (x - width / 2) / scale + 4, (y - height / 2) / scale + 1);
            });
        };
        var prevTime;
        var pastFrameTimes = [];
        const detectFrame = function () {
            if (!workerId) return requestAnimationFrame(detectFrame);
            const image = new CVImage(video);
            inferEngine
                .infer(workerId, image)
                .then(function (predictions) {
                    requestAnimationFrame(detectFrame);
                    renderPredictions(predictions);
                    if (prevTime) {
                        pastFrameTimes.push(Date.now() - prevTime);
                        if (pastFrameTimes.length > 30) pastFrameTimes.shift();
                        var total = 0;
                        _.each(pastFrameTimes, function (t) { total += t / 1000; });
                        var fps = pastFrameTimes.length / total;
                        $("#fps").text(Math.round(fps));
                    }
                    prevTime = Date.now();
                })
                .catch(function (e) {
                    console.log("CAUGHT", e);
                    requestAnimationFrame(detectFrame);
                });
        };
    });
    </script>
</body>
</html>
