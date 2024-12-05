document.addEventListener("DOMContentLoaded", async function () {
    const topUsers = await fetchTop10UsersFromServer();
    if (topUsers?.success && topUsers.data) {
        const processedUsers = topUsers.data.map((user, index) => ({
            name: `${user.userName} ${user.userSurname}`,
            initials: getInitials(user.userName, user.userSurname),
            rating: user.userLTStars,
            rank: `#${index + 1}`,
        }));
        loadTop10UserIntoTable(processedUsers);
    } else {
        console.error("Erro ao carregar usuários top 10 ou dados inválidos.");
    }
});

function getInitials(name, surname) {
    const nameInitial = name && name.trim() ? name.trim()[0].toUpperCase() : "N";
    const surnameInitial = surname && surname.trim() ? surname.trim()[0].toUpperCase() : "A";
    return nameInitial + surnameInitial;
}

function loadTop10UserIntoTable(topUsers) {
    const tbody = document.getElementById("tbody_tp10");

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
        ratingValue.textContent = parseInt(user.rating).toLocaleString("pt-PT");

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


    hideBigOverlayLoader();
}


function hideBigOverlayLoader() {
    document.getElementById("full_container_loading").style.display = "none";
    document.body.style.overflow = 'auto';
    document.querySelector('.progress').style.animation = 'none';
}


async function fetchTop10UsersFromServer() {
    try {
        const response = await fetch("http://localhost/LonyExtra/0/api/dashboard/GetTop10Users.php", {
            method: "GET",
            headers: {
                "Content-Type": "application/json"
            }
        });

        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.status}`);
        }

        const top10Response = await response.json();
        return top10Response;
    } catch (error) {
        console.error("Erro ao buscar os dados do servidor:", error);
        return null;
    }
}
