let contadorServicos = 1;

function adicionarServico() {
    const div = document.createElement("div");
    div.className = "servico";
    div.innerHTML = `
        <label>Tipo de Serviço:</label>
        <select name="servicos[${contadorServicos}][tipo_servico]" required>
            <?php
            $tipos_servicos->data_seek(0); // Reinicia o ponteiro para que os tipos sejam novamente exibidos
            while ($tipo_servico = $tipos_servicos->fetch_assoc()) {
                echo "<option value='" . $tipo_servico['nome'] . "'>" . $tipo_servico['nome'] . "</option>";
            }
            ?>
        </select>
        <label>Militar:</label>
        <select name="servicos[${contadorServicos}][id_militar]" required>
            <?php
            $militares->data_seek(0); // Reinicia o ponteiro para que os militares sejam exibidos novamente
            while ($militar = $militares->fetch_assoc()) {
                echo "<option value='" . $militar['id'] . "'>" . $militar['nome'] . "</option>";
            }
            ?>
        </select>
    `;
    document.getElementById("servicos").appendChild(div);
    contadorServicos++;
}
