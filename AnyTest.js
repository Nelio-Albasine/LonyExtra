const secretKey = process.env.LONY_EXTRA_POINTS_SECRET_KEY;

if (secretKey) {
    console.log(`A chave secreta é: ${secretKey}`);
} else {
    console.error('A variável de ambiente LONY_EXTRA_POINTS_SECRET_KEY não está definida.');
}
