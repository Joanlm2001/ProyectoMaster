const API_URL = "https://pokeapi.co/api/v2/pokemon/";

function getPokemons() {
    return fetch(API_URL)
        .then((response) => response.json())
        .then((data) => data.results);
}

function createPokemonCard(pokemon) {
    const article = document.createRange().createContextualFragment(
        /*html*/
        `
      <article class="pokemon-card">
        <img src="${pokemon.sprites?.front_default}" alt="${pokemon.name}">
        <h2>${pokemon.name}</h2>
      </article>
    `
    );

    const pokemonDetailsURL = `https://pokeapi.co/api/v2/pokemon/${pokemon.name}`;

    fetch(pokemonDetailsURL)
        .then((response) => response.json())
        .then((pokemonDetails) => {
            const imgElement = article.querySelector("img");
            imgElement.src = pokemonDetails.sprites.front_default;
        })
        .catch((error) => {
            console.error("Error fetching Pokémon details:", error);
        });

    return article;
}

function showPokemons(pokemons) {
    const pokemonSection = document.querySelector("#pokemons");
    pokemons.forEach((pokemon) => {
        const card = createPokemonCard(pokemon);
        if (card) {
            pokemonSection.appendChild(card);
        }
    });
}

getPokemons().then((pokemons) => {
    showPokemons(pokemons);
});
