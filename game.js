const games = [
    {
        name: "Grand Theft Auto V",
        page: "gta.html",
        image: "https://upload.wikimedia.org/wikipedia/en/a/a5/Grand_Theft_Auto_V.png"
    },
    {
        name: "Red Dead Redemption 2",
        page: "rdr.html",
        image: "https://upload.wikimedia.org/wikipedia/en/4/44/Red_Dead_Redemption_II.jpg"
    },
    {
        name: "Elden Ring",
        page: "elden.html",
        image: "https://upload.wikimedia.org/wikipedia/en/b/b9/Elden_Ring_Box_art.jpg"
    },
    {
        name: "Resident Evil 4 Remake",
        page: "re4.html",
        image: "https://upload.wikimedia.org/wikipedia/en/d/df/Resident_Evil_4_remake_cover_art.jpg"
    },
    {
        name: "Cyberpunk 2077",
        page: "cyberpunk.html",
        image: "https://upload.wikimedia.org/wikipedia/en/9/9f/Cyberpunk_2077_box_art.jpg"
    },
    {
        name: "Bloodborne",
        page: "bloodborne.html",
        image: "https://upload.wikimedia.org/wikipedia/en/6/68/Bloodborne_Cover_Wallpaper.jpg"
    },
    {
        name: "Sekiro Shadows Die Twice",
        page: "sekiro.html",
        image: "https://upload.wikimedia.org/wikipedia/en/6/6e/Sekiro_art.jpg"
    },
    {
        name: "Ghost of Tsushima",
        page: "Ghost.html",
        image: "https://upload.wikimedia.org/wikipedia/en/b/b6/Ghost_of_Tsushima.jpg"
    },
    {
        name: "FC 26",
        page: "fc26.html",
        image: "https://image.api.playstation.com/vulcan/ap/rnd/202507/1617/f0fe830f8f01600d13cce060680e0287374c58613a63c716.png"
    },
    {
        name: "Uncharted 4",
        page: "Uncharted.html",
        image: "https://upload.wikimedia.org/wikipedia/en/1/1a/Uncharted_4_box_artwork.jpg"
    },
    {
        name: "The Last of Us",
        page: "tlou.html",
        image: "https://upload.wikimedia.org/wikipedia/en/4/46/Video_Game_Cover_-_The_Last_of_Us.jpg"
    },
    {
        name: "God of War Ragnarök",
        page: "gow.html",
        image: "https://upload.wikimedia.org/wikipedia/en/e/ee/God_of_War_Ragnar%C3%B6k_cover.jpg"
    },
    {
        name: "Marvel Spider-Man 2",
        page: "sm2.html",
        image: "https://upload.wikimedia.org/wikipedia/en/0/0f/SpiderMan2PS5BoxArt.jpeg"
    },
    {
        name: "The Witcher 3 Wild Hunt",
        page: "witcher3.html",
        image: "https://upload.wikimedia.org/wikipedia/commons/0/0b/The_Witcher_3_-_Standard_Edition_Unboxing_%28Official_Trailer%29_cover.jpg"
    },
    {
        name: "Assassin's Creed IV Black Flag",
        page: "acbf.html",
        image: "https://upload.wikimedia.org/wikipedia/en/2/28/Assassin%27s_Creed_IV_-_Black_Flag_cover.jpg"
    },
    {
        name: "Minecraft",
        page: "minecraft.html",
        image: "https://www.metacritic.com/a/img/resize/2632ef55e90ff9375b4ca536ad814726741a573b/catalog/provider/6/12/6-1-702279-52.jpg?auto=webp&fit=cover&height=264&width=176"
    },
    {
        name: "Mortal Kombat 1",
        page: "mk1.html",
        image: "https://upload.wikimedia.org/wikipedia/en/5/5b/Mortal_Kombat_1_key_art.jpeg"
    },
    {
        name: "Call of Duty Modern Warfare Trilogy",
        page: "MWT.html",
        image: "https://cdn2.steamgriddb.com/thumb/07d253164fa54f7f2c4e801a06696134.jpg"
    },
    {
        name: "Call of Duty Modern Warfare",
        page: "cod.html",
        image: "https://upload.wikimedia.org/wikipedia/en/4/44/Call_of_Duty_Modern_Warfare_II_Key_Art.jpg"
    },
    {
        name: "Call of Duty Black Ops II",
        page: "codbo2.html",
        image: "https://upload.wikimedia.org/wikipedia/en/0/05/Call_of_Duty_Black_Ops_II_box_artwork.png"
    },
    {
        name: "Batman Arkham City",
        page: "bmac.html",
        image: "https://upload.wikimedia.org/wikipedia/en/0/00/Batman_Arkham_City_Game_Cover.jpg"
    },
    {
        name: "WWE 2K26",
        page: "wwe.html",
        image: "https://upload.wikimedia.org/wikipedia/en/4/4f/WWE_2K26_standard_cover.jpeg"
    },
    {
        name: "Outlast",
        page: "outlast.html",
        image: "https://upload.wikimedia.org/wikipedia/en/a/aa/Outlast_cover.jpg"
    },
    {
        name: "Mafia Trilogy",
        page: "mafia.html",
        image: "https://cdn2.unrealengine.com/Diesel%2Fbundles%2Fmafia-trilogy%2FEGS_MafiaTrilogy_Hangar13_S2-1200x1600-cc4971fd3a4fd7cab997c42804d981c08e83b13b.jpg"
    },
    {
        name: "Hollow Knight",
        page: "HollowKnight.html",
        image: "https://upload.wikimedia.org/wikipedia/en/d/de/Hollow_Knight_2026_cover_art.jpg"
    },
    {
        name: "Devil May Cry 5",
        page: "DMC5.html",
        image: "https://upload.wikimedia.org/wikipedia/en/c/cb/Devil_May_Cry_5.jpg"
    },
    {
        name: "Gran Turismo 7",
        page: "GT7.html",
        image: "https://upload.wikimedia.org/wikipedia/en/1/14/Gran_Turismo_7_cover_art.jpg"
    },
    {
        name: "ARC Raiders",
        page: "Arc.html",
        image: "https://upload.wikimedia.org/wikipedia/en/7/73/Arc_Raiders_cover_art.jpg"
    },
    {
        name: "Stardew Valley",
        page: "Stardew.html",
        image: "https://upload.wikimedia.org/wikipedia/en/f/fd/Logo_of_Stardew_Valley.png"
    },
    {
        name: "Cuphead",
        page: "cup.html",
        image: "https://upload.wikimedia.org/wikipedia/en/e/eb/Cuphead_%28artwork%29.png"
    },
    {
        name: "Psychonauts 2",
        page: "Psychonauts2.html",
        image: "https://upload.wikimedia.org/wikipedia/en/2/23/Psychonauts_2_cover.png"
    },
    {
        name: "Invincible VS",
        page: "invincible.html",
        image: "https://upload.wikimedia.org/wikipedia/en/8/84/Invincible_VS_cover_art.jpeg"
    },
    {
        name: "The Evil Within",
        page: "EW.html",
        image: "https://upload.wikimedia.org/wikipedia/en/5/56/The_Evil_Within_boxart.jpg"
    }
];

/* ================= HEADER LOAD ================= */

fetch("game.html")
.then(response => response.text())
.then(data => {

    document.getElementById("game-container").innerHTML = data;

    /* ================= SEARCH ELEMENTS ================= */

    const searchBtn = document.getElementById('searchToggle');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    /* ================= SEARCH TOGGLE ================= */

    if (searchBtn && searchInput && searchResults) {

        searchBtn.addEventListener('click', () => {

            searchInput.classList.toggle('active');

            if(searchInput.classList.contains('active')){

                searchInput.focus();

            }else{

                searchInput.value = '';
                searchResults.style.display = 'none';
            }
        });
    }

    /* ================= SEARCH INPUT ================= */

    if (searchInput && searchResults) {

        searchInput.addEventListener("input", function () {

            const value = this.value.toLowerCase().trim();

            searchResults.innerHTML = "";

            if (value === "") {

                searchResults.style.display = "none";
                return;
            }

            const filteredGames = games.filter(game =>
                game.name.toLowerCase().includes(value)
            );

            if (filteredGames.length === 0) {

                searchResults.style.display = "none";
                return;
            }

            filteredGames.forEach(game => {

                const item = document.createElement("div");

                item.innerHTML = `
                    <div style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                    ">

                        <img
                            src="${game.image}"
                            onerror="this.src='https://via.placeholder.com/90x90/111111/930505?text=GAME'"
                            style="
                                width:45px;
                                height:45px;
                                border-radius:10px;
                                object-fit:cover;
                                flex-shrink:0;
                            "
                        >

                        <span>${game.name}</span>

                    </div>
                `;

                item.onclick = () => {

                    window.location.href = game.page;
                };

                searchResults.appendChild(item);
            });

            searchResults.style.display = "block";
        });
    }

    /* ================= CLOSE SEARCH ================= */

    document.addEventListener("click", function(e){

        const searchBar = document.querySelector(".chat-tools-bar");

        if (searchBar && searchResults && !searchBar.contains(e.target)) {

            searchResults.style.display = "none";
        }
    });

});

/* ================= CART ================= */

function addToCart(name, price, image){

    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    cart.push({
        name:name,
        price:price,
        image:image
    });

    localStorage.setItem("cart", JSON.stringify(cart));

    const btn = event && event.target ? event.target.closest('button') : null;

    if (btn) {

        const oldHtml = btn.innerHTML;

        btn.innerHTML = `
            <i class="fa-solid fa-check"></i>
            <span>Added!</span>
        `;

        setTimeout(() => {

            btn.innerHTML = oldHtml;

        }, 1500);
    }

    alert("✓ Added To Cart");
}