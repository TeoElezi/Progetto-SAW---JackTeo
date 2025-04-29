const targetDate = new Date(raceDate).getTime();
const countdown = setInterval(() => {
    const now = new Date().getTime();
    const distance = targetDate - now;

    if (distance < 0) {
        document.getElementById("countdown").innerHTML = "Gara in corso o conclusa!";
        clearInterval(countdown);
    } else {
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        document.getElementById("countdown").innerHTML = `${days} giorni, ${hours} ore, ${minutes} minuti`;
    }
}, 1000);
document.addEventListener("DOMContentLoaded", () => {
    const countdownElement = document.getElementById("countdown");
    if (countdownElement) {
        countdownElement.innerHTML = "Caricamento...";
    }
});