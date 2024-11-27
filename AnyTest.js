let valorIncial = 1452
let valorTotal = 1527

let valorFaltando = valorTotal - valorIncial

console.log(`O inicial valor que está faltando é de: ${valorFaltando.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}`)