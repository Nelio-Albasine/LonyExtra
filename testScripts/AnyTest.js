let values = {
    "energia": 600,
    "Saco de carvao": 410,
    "Vassoura de Dentro": 170,
    "Vassoura de 100": 100,
    "Divida Édio": 130,
    "Divida T Fofi": 250,
    "Divida T Sonia": 310,
    "Brother": 510,
    "Mother": 410,
};

function calculateTotal(inventory) {
    let total = 0;
    for (let item in inventory) {
        total += inventory[item];
    }
    return total;
}

let totalValue = calculateTotal(values);
console.log("=== Total do Inventário ===");
console.log(`Valor Total: ${totalValue}`);
console.log("===========================");
