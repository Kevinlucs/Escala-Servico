let contadorServicos = 1;

function adicionarServico() {
    const div = document.createElement("div");
    div.className = "servico";
    div.innerHTML = `
        <label>Tipo de Serviço:</label>
        <select name="servicos[${contadorServicos}][tipo_servico]" class="tipo_servico" required>
            <?php
            // Exibe os tipos de serviço disponíveis novamente
            $tipos_servicos->data_seek(0); // Reinicia o ponteiro para que os tipos sejam novamente exibidos
            while ($tipo_servico = $tipos_servicos->fetch_assoc()) {
                echo "<option value='" . $tipo_servico['nome'] . "'>" . $tipo_servico['nome'] . "</option>";
            }
            ?>
        </select>
        <label>Militar:</label>
        <select name="servicos[${contadorServicos}][id_militar]" class="militar_select" required>
            <option value="">Selecione o tipo de serviço primeiro</option>
        </select>
    `;

    // Append the new service div
    document.getElementById("servicos").appendChild(div);

    // Adding event listener to the new 'tipo_servico' select dropdown
    div.querySelector(".tipo_servico").addEventListener("change", function () {
        atualizarMilitares(contadorServicos);
    });

    // Increment the service counter
    contadorServicos++;
}

// This function will fetch the appropriate military personnel for the selected service
function atualizarMilitares(servicoIndex) {
    const tipoServico = document.querySelector(
        `select[name="servicos[${servicoIndex}][tipo_servico]"]`
    ).value;
    const militarSelect = document.querySelector(
        `select[name="servicos[${servicoIndex}][id_militar]"]`
    );

    // Clear previous military options
    militarSelect.innerHTML = '<option value="">Carregando...</option>';

    if (tipoServico) {
        // Use AJAX to fetch the appropriate military options
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "gerenciar_escala.php", true);
        xhr.setRequestHeader(
            "Content-Type",
            "application/x-www-form-urlencoded"
        );
        xhr.onload = function () {
            if (xhr.status === 200) {
                const militares = JSON.parse(xhr.responseText);
                militarSelect.innerHTML = ""; // Clear the loading text

                // Populate military options
                militares.forEach(function (militar) {
                    const option = document.createElement("option");
                    option.value = militar.id;
                    option.textContent = `${militar.posto_graduacao} ${militar.nome}`;
                    militarSelect.appendChild(option);
                });
            }
        };
        xhr.send(`tipo_servico=${tipoServico}`);
    } else {
        militarSelect.innerHTML =
            '<option value="">Selecione o tipo de serviço primeiro</option>';
    }
}
