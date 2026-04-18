function calculateScore(correctAnswer, studentAnswer) {
    if(studentAnswer.trim().toLowerCase() === correctAnswer.toLowerCase()) {
        alert("Correct! Progress updated.");
    } else {
        alert("Incorrect. Review the module again.");
    }
}