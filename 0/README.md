# IMPORTANT
O BIG loader, que sobrepõe a página toda, é cancelado quando os top 10 users são carregados. Isso ocorre porque a consulta para carregar os top 10 users é mais demorada do que as demais, e essa consulta é necessária.

O nome da função que lida com isso é: hideBigOverlayLoader().
===============================================================
