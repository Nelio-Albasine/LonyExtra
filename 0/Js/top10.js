document.addEventListener("DOMContentLoaded", () => {
    loadTop10UserIntoTable();
});


function loadTop10UserIntoTable() {
    const tbody = document.getElementById("tbody_tp10");

    const topUsers = [
        { initials: "NA", name: "Natasha Adams", rating: "4.5k", rank: "Top 01" },
        { initials: "JD", name: "John Doe", rating: "3.8k", rank: "Top 02" },
        { initials: "AS", name: "Alice Smith", rating: "3.5k", rank: "Top 03" },
        { initials: "BM", name: "Bruce Miller", rating: "2.9k", rank: "Top 04" },
        { initials: "KW", name: "Karen White", rating: "2.5k", rank: "Top 05" },
    ];

    const getRandomColor = () => {
        const letters = "0123456789ABCDEF";
        let color = "#";
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    };

    topUsers.forEach((user) => {
        const row = document.createElement("tr");

        const cell = document.createElement("td");

        const cardLayout = document.createElement("div");
        cardLayout.className = "card_layout";

        const profileInitials = document.createElement("div");
        profileInitials.className = "profile_initials";

        profileInitials.style.backgroundColor = getRandomColor();

        const initialsText = document.createElement("p");
        initialsText.textContent = user.initials;

        profileInitials.appendChild(initialsText);

        const userInfo = document.createElement("div");
        userInfo.className = "user_info";

        const baseNameAndRating = document.createElement("div");
        baseNameAndRating.className = "base_name_and_rating";

        const userName = document.createElement("p");
        userName.className = "user_top10_name";
        userName.textContent = user.name;

        const rating = document.createElement("div");
        rating.className = "rating";

        const ratingValue = document.createElement("span");
        ratingValue.className = "rating-value";
        ratingValue.textContent = user.rating;

        const ratingIcon = document.createElement("span");
        ratingIcon.className = "rating_icon";
        ratingIcon.textContent = "⭐";

        rating.appendChild(ratingValue);
        rating.appendChild(ratingIcon);

        baseNameAndRating.appendChild(userName);
        baseNameAndRating.appendChild(rating);

        const socialInfo = document.createElement("div");
        socialInfo.className = "social_info";

        const trophyIcon = document.createElement("span");
        trophyIcon.className = "trophy_icon";
        trophyIcon.textContent = "🏆";

        const topRanking = document.createElement("span");
        topRanking.className = "top_ranking";
        topRanking.textContent = user.rank;

        socialInfo.appendChild(trophyIcon);
        socialInfo.appendChild(topRanking);

        userInfo.appendChild(baseNameAndRating);
        userInfo.appendChild(socialInfo);

        cardLayout.appendChild(profileInitials);
        cardLayout.appendChild(userInfo);

        cell.appendChild(cardLayout);
        row.appendChild(cell);

        tbody.appendChild(row);
    });
}
