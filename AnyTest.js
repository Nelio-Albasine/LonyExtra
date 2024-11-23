async function currentuserTime() {
    try {
        const response = await fetch("http://worldtimeapi.org/api/ip");
        if (!response.ok) {
            throw new Error("Erro ao obter dados da API.");
        }
        const data = await response.json();
        console.log("Fuso horário detectado pela API:", data.timezone);
        console.log("Data e hora atual:", data.datetime);
    } catch (error) {
        console.error("Erro ao obter fuso horário da API:", error);
    }
}

currentuserTime();
