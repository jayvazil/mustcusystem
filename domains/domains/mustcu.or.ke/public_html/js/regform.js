document.getElementById('identity').addEventListener('change', function () {
    const value = this.value;
    const ministryDiv = document.getElementById('ministryDiv');
    const leaderDiv = document.getElementById('leaderDiv');

    if (value === 'yes') {
        ministryDiv.style.display = 'block';
        leaderDiv.style.display = 'none';
    } else if (value === 'no') {
        ministryDiv.style.display = 'none';
        leaderDiv.style.display = 'block';
    } else {
        ministryDiv.style.display = 'none';
        leaderDiv.style.display = 'none';
    }
});

const form = document.getElementById('cuForm');
form.addEventListener('submit', function (e) {
    e.preventDefault();
    alert('Form submitted successfully!');
});
