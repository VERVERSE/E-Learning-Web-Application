const localVideo = document.getElementById('localVideo');
const remoteVideo = document.getElementById('remoteVideo');
const roomId = document.getElementById('roomId').value;
let peerConnection;
let localStream;

// Get buttons
const cameraButton = document.getElementById('cameraButton');
const micButton = document.getElementById('micButton');
const startButton = document.getElementById('startButton');
const endButton = document.getElementById('endButton');
const startClassButton = document.getElementById('startClassButton');

// Get containers
const controlsDiv = document.getElementById('controls');
const videosDiv = document.getElementById('videos');

// Track whether the camera and microphone are on
let cameraOn = true;
let micOn = true;

// Add event listener for Start Class button
startClassButton.addEventListener('click', () => {
    startClass();
});

// Event listener for Start Video
startButton.addEventListener('click', () => {
    startVideo();
});

// Event listener for End Video
endButton.addEventListener('click', () => {
    endVideo();
});

// Event listener for Camera On/Off button
cameraButton.addEventListener('click', () => {
    toggleCamera();
});

// Event listener for Mic On/Off button
micButton.addEventListener('click', () => {
    toggleMicrophone();
});

// Function to start the class (reveal buttons and videos)
function startClass() {
    // Reveal the controls and video sections
    controlsDiv.style.display = 'block';
    videosDiv.style.display = 'block';

    // Hide the Start Class button
    startClassButton.style.display = 'none';

    // Start the video stream
    startVideo();
}

// Start the video stream
function startVideo() {
    // Request access to the camera and microphone
    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then((stream) => {
            localVideo.srcObject = stream;
            localStream = stream;

            // Enable the End Video button and disable Start Video
            startButton.disabled = true;
            endButton.disabled = false;

            // Create WebRTC Peer Connection
            peerConnection = new RTCPeerConnection(config);

            // Add local stream to the connection
            stream.getTracks().forEach(track => peerConnection.addTrack(track, stream));

            // Handle ICE candidates
            peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    sendSignalingData('candidate', event.candidate);
                }
            };

            // Handle remote stream
            peerConnection.ontrack = (event) => {
                remoteVideo.srcObject = event.streams[0];
            };

            // Create or join a room
            createOrJoinRoom();
        })
        .catch(error => console.error('Error accessing media devices:', error));
}

// End the video stream
function endVideo() {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }

    startButton.disabled = false;
    endButton.disabled = true;
    localVideo.srcObject = null;
}

// Toggle the camera (on/off)
function toggleCamera() {
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack.enabled) {
            videoTrack.enabled = false;
            cameraButton.textContent = "Turn On Camera";
            cameraOn = false;
        } else {
            videoTrack.enabled = true;
            cameraButton.textContent = "Turn Off Camera";
            cameraOn = true;
        }
    }
}

// Toggle the microphone (on/off)
function toggleMicrophone() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack.enabled) {
            audioTrack.enabled = false;
            micButton.textContent = "Turn On Microphone";
            micOn = false;
        } else {
            audioTrack.enabled = true;
            micButton.textContent = "Turn Off Microphone";
            micOn = true;
        }
    }
}

// Send signaling data (offer, answer, candidate) via PHP
function sendSignalingData(type, data) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'signaling.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(`roomId=${roomId}&type=${type}&data=${JSON.stringify(data)}`);
}

// Create or join a room (poll for offer/answer)
function createOrJoinRoom() {
    peerConnection.createOffer()
        .then(offer => {
            peerConnection.setLocalDescription(offer);
            sendSignalingData('offer', offer);
        });

    setInterval(() => {
        fetchSignalingData('answer');
        fetchSignalingData('offer');
    }, 2000);
}

// Fetch signaling data from PHP
function fetchSignalingData(type) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `signaling.php?roomId=${roomId}&type=${type}`, true);
    xhr.onload = () => {
        if (xhr.status === 200 && xhr.responseText) {
            const data = JSON.parse(xhr.responseText);
            if (type === 'offer') {
                peerConnection.setRemoteDescription(new RTCSessionDescription(data));
                peerConnection.createAnswer()
                    .then(answer => {
                        peerConnection.setLocalDescription(answer);
                        sendSignalingData('answer', answer);
                    });
            } else if (type === 'answer') {
                peerConnection.setRemoteDescription(new RTCSessionDescription(data));
            } else if (type === 'candidate') {
                peerConnection.addIceCandidate(new RTCIceCandidate(data));
            }
        }
    };
    xhr.send();
}




//massage//
const chatWindow = document.getElementById('chatWindow');
const messageInput = document.getElementById('messageInput');
const sendButton = document.getElementById('sendButton');

// Send message via AJAX
sendButton.addEventListener('click', () => {
    const message = messageInput.value;
    if (message.trim() !== '') {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'send_message.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (this.status == 200) {
                messageInput.value = ''; // Clear input
                loadMessages(); // Reload chat messages
            }
        };
        xhr.send(`message=${message}`);
    }
});

// Function to load chat messages via AJAX
function loadMessages() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'load_messages.php', true);
    xhr.onload = function () {
        if (this.status == 200) {
            chatWindow.innerHTML = this.responseText;
            chatWindow.scrollTop = chatWindow.scrollHeight; // Auto-scroll to the latest message
        }
    };
    xhr.send();
}

// Automatically reload messages every 2 seconds
setInterval(loadMessages, 2000);

