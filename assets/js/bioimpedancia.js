// Função para classificar IMC
function classificarIMC(imc) {
    if (imc < 18.5) {
        return "Abaixo do peso";
    } else if (imc < 25) {
        return "Peso adequado";
    } else if (imc < 30) {
        return "Sobrepeso";
    } else if (imc < 35) {
        return "Obesidade grau I";
    } else if (imc < 40) {
        return "Obesidade grau II";
    } else {
        return "Obesidade grau III";
    }
}

// Obtém e calcula estimativas relacionadas ao uso de Monjaro
function obterDadosMonjaro() {
    var checkbox = document.getElementById("usa-monjarocheck");
    var doseSelect = document.getElementById("monjaro-dose");
    var freqSelect = document.getElementById("monjaro-frequencia");
    var dataInput = document.getElementById("monjaro-ultima-data");

    if (!checkbox || !checkbox.checked) {
        return {
            ativo: false
        };
    }

    var dose = doseSelect && doseSelect.value ? parseFloat(doseSelect.value.replace(",", ".")) : null;
    var frequencia = freqSelect ? freqSelect.value : "1x_semana";
    var dataStr = dataInput ? dataInput.value : "";

    if (!dose || isNaN(dose)) {
        return {
            ativo: false
        };
    }

    var fatorFrequencia;
    if (frequencia === "1x_semana") {
        fatorFrequencia = 1;
    } else if (frequencia === "1x_10dias") {
        fatorFrequencia = 7 / 10;
    } else if (frequencia === "1x_14dias") {
        fatorFrequencia = 7 / 14;
    } else {
        fatorFrequencia = 1;
    }

    var mgSemanaEquivalente = dose * fatorFrequencia;

    // 5 mg/sem → ~0,8 kg/sem → 0,8 / 5 = 0,16 kg por mg/sem
    var perdaKgPorMgSemana = 0.16;
    var perdaSemBaseKg = mgSemanaEquivalente * perdaKgPorMgSemana;

    var fatorAtivo = 1;
    var diasDesdeUltima = null;

    if (dataStr) {
        var hoje = new Date();
        var dataUltima = new Date(dataStr + "T00:00:00");
        if (!isNaN(dataUltima.getTime())) {
            var diffMs = hoje.getTime() - dataUltima.getTime();
            diasDesdeUltima = diffMs / (1000 * 60 * 60 * 24);

            if (diasDesdeUltima <= 7) {
                fatorAtivo = 1;
            } else if (diasDesdeUltima >= 28) {
                fatorAtivo = 0.2;
            } else {
                var proporcao = (diasDesdeUltima - 7) / (28 - 7);
                proporcao = Math.max(0, Math.min(1, proporcao));
                fatorAtivo = 1 - proporcao * 0.8;
            }
        }
    }

    var perdaSemKg = perdaSemBaseKg * fatorAtivo;
    if (perdaSemKg < 0) {
        perdaSemKg = 0;
    }

    var kcalPorKg = 7700;
    var deficitSemKcal = perdaSemKg * kcalPorKg;
    var deficitDiaKcal = deficitSemKcal / 7;

    return {
        ativo: true,
        dose: dose,
        frequencia: frequencia,
        diasDesdeUltima: diasDesdeUltima,
        mgSemanaEquivalente: mgSemanaEquivalente,
        perdaSemKg: perdaSemKg,
        deficitSemKcal: deficitSemKcal,
        deficitDiaKcal: deficitDiaKcal
    };
}

// Atualiza card de resumo do Monjaro
function atualizarCardMonjaro(dadosMonjaro) {
    var card = document.getElementById("card-monjaro-resumo");
    var mainEl = document.getElementById("res-monjaro-main");
    var extraEl = document.getElementById("res-monjaro-extra");
    var pillsEl = document.getElementById("res-monjaro-pills");

    if (!card || !mainEl || !extraEl || !pillsEl) {
        return;
    }

    pillsEl.innerHTML = "";

    if (!dadosMonjaro || !dadosMonjaro.ativo || !dadosMonjaro.perdaSemKg || dadosMonjaro.perdaSemKg <= 0) {
        card.style.display = "none";
        mainEl.textContent = "—";
        extraEl.textContent = "—";
        return;
    }

    card.style.display = "block";

    mainEl.textContent = dadosMonjaro.perdaSemKg.toFixed(2) + " kg/sem (estimativa com a dose informada)";

    var textoExtra = "Dose equivalente de aproximadamente " +
        dadosMonjaro.mgSemanaEquivalente.toFixed(1) + " mg/sem. " +
        "Déficit energético aproximado de " +
        Math.round(dadosMonjaro.deficitSemKcal) + " kcal/sem (" +
        Math.round(dadosMonjaro.deficitDiaKcal) + " kcal/dia).";

    if (typeof dadosMonjaro.diasDesdeUltima === "number") {
        textoExtra += " Última aplicação há cerca de " +
            dadosMonjaro.diasDesdeUltima.toFixed(0) + " dia(s).";
    }

    extraEl.textContent = textoExtra;

    var pillModoEstavel = document.createElement("span");
    pillModoEstavel.className = "res-pill";
    pillModoEstavel.textContent = "Estimativa baseada em perda média populacional";
    pillsEl.appendChild(pillModoEstavel);

    if (typeof dadosMonjaro.diasDesdeUltima === "number") {
        if (dadosMonjaro.diasDesdeUltima <= 7) {
            var pillRecente = document.createElement("span");
            pillRecente.className = "res-pill";
            pillRecente.textContent = "Efeito mais recente";
            pillsEl.appendChild(pillRecente);
        } else if (dadosMonjaro.diasDesdeUltima > 14) {
            var pillLonge = document.createElement("span");
            pillLonge.className = "res-pill warning";
            pillLonge.textContent = "Aplicação mais distante · efeito pode estar menor";
            pillsEl.appendChild(pillLonge);
        }
    }
}

// Função principal de cálculo
function calcularDeficit(event) {
    event.preventDefault();

    var peso = parseFloat(document.getElementById("peso").value.replace(",", "."));
    var altura = parseFloat(document.getElementById("altura").value.replace(",", "."));
    var idade = parseInt(document.getElementById("idade").value, 10);
    var sexo = document.getElementById("sexo").value;
    var atividade = parseFloat(document.getElementById("atividade").value);
    var caloriasConsumidasInput = document.getElementById("calorias-consumidas").value;
    var caloriasConsumidas = caloriasConsumidasInput ? parseFloat(caloriasConsumidasInput.replace(",", ".")) : null;

    var erroEl = document.getElementById("erro");
    var resultadosEl = document.getElementById("resultados");

    erroEl.classList.remove("show");
    erroEl.textContent = "";

    if (!peso || !altura || !idade || !sexo) {
        erroEl.textContent = "Preencha peso, altura, idade e sexo para calcular.";
        erroEl.classList.add("show");
        resultadosEl.classList.remove("show");
        return;
    }

    if (peso <= 0 || altura <= 0 || idade <= 0) {
        erroEl.textContent = "Verifique se os valores digitados fazem sentido (peso, altura, idade).";
        erroEl.classList.add("show");
        resultadosEl.classList.remove("show");
        return;
    }

    var bmr;
    if (sexo === "M") {
        bmr = 10 * peso + 6.25 * altura - 5 * idade + 5;
    } else if (sexo === "F") {
        bmr = 10 * peso + 6.25 * altura - 5 * idade - 161;
    } else {
        bmr = 10 * peso + 6.25 * altura - 5 * idade;
    }

    var tdee = bmr * atividade;

    var alturaM = altura / 100;
    var imc = peso / (alturaM * alturaM);

    var minKcal;
    if (sexo === "F") {
        minKcal = Math.max(1200, bmr * 0.8);
    } else if (sexo === "M") {
        minKcal = Math.max(1400, bmr * 0.8);
    } else {
        minKcal = Math.max(1300, bmr * 0.8);
    }

    var alvoLento = Math.max(tdee - 400, minKcal);
    var alvoModerado = Math.max(tdee - 500, minKcal);
    var alvoMaisAgressivo = Math.max(tdee - 750, minKcal);

    var deficitDia = null;
    if (caloriasConsumidas !== null && !isNaN(caloriasConsumidas)) {
        deficitDia = tdee - caloriasConsumidas;
    }

    var resBasalEl = document.getElementById("res-basal");
    var resTdeeEl = document.getElementById("res-tdee");
    var resBasalPillEl = document.getElementById("res-basal-pill");
    var resTdeePillEl = document.getElementById("res-tdee-pill");
    var resImcEl = document.getElementById("res-imc");
    var resImcExtraEl = document.getElementById("res-imc-extra");
    var resImcPillEl = document.getElementById("res-imc-pill");
    var resDeficitDiaEl = document.getElementById("res-deficit-dia");
    var resDeficitExtraEl = document.getElementById("res-deficit-extra");
    var resDeficitPillsEl = document.getElementById("res-deficit-pills");
    var resDeficitSugEl = document.getElementById("res-deficit-sug");
    var resDeficitSugExtraEl = document.getElementById("res-deficit-sug-extra");
    var metricBmrEl = document.getElementById("metric-bmr");
    var metricTdeeEl = document.getElementById("metric-tdee");

    resBasalEl.textContent = Math.round(bmr) + " kcal/dia · Taxa Metabólica Basal";
    resTdeeEl.textContent = Math.round(tdee) + " kcal/dia · Gasto total estimado (TDEE)";
    resBasalPillEl.textContent = "Corpo em repouso";
    resTdeePillEl.textContent = "Inclui rotina/atividade";

    metricBmrEl.textContent = Math.round(bmr) + " kcal";
    metricTdeeEl.textContent = Math.round(tdee) + " kcal";

    var imcClassificacao = classificarIMC(imc);
    resImcEl.textContent = imc.toFixed(1) + " · " + imcClassificacao;
    resImcExtraEl.textContent = "IMC estimado com base em " + peso.toFixed(1) + " kg e " + altura.toFixed(1) + " cm.";
    resImcPillEl.textContent = "Apenas uma referência. Bioimpedância é mais precisa para composição corporal.";

    resDeficitPillsEl.innerHTML = "";
    if (deficitDia === null || isNaN(deficitDia)) {
        resDeficitDiaEl.textContent = "Informe as calorias consumidas hoje para ver o déficit.";
        resDeficitExtraEl.textContent = "O déficit é calculado como TDEE menos o total de calorias ingeridas ao longo do dia.";
    } else {
        var deficitAbs = Math.abs(deficitDia);
        if (deficitDia > 0) {
            resDeficitDiaEl.textContent = deficitAbs.toFixed(0) + " kcal · Déficit estimado hoje";
            resDeficitExtraEl.textContent = "Você ingeriu aproximadamente " + caloriasConsumidas.toFixed(0) +
                " kcal. Em relação ao gasto estimado (" + Math.round(tdee) + " kcal), isso indica déficit de energia.";
            var pill1 = document.createElement("span");
            pill1.className = "res-pill";
            pill1.textContent = "Déficit (corpo tende a utilizar reservas de energia)";
            resDeficitPillsEl.appendChild(pill1);

            if (deficitAbs > 1000) {
                var pill2 = document.createElement("span");
                pill2.className = "res-pill warning";
                pill2.textContent = "Déficits muito altos por muitos dias seguidos podem não ser sustentáveis.";
                resDeficitPillsEl.appendChild(pill2);
            }
        } else if (deficitDia < 0) {
            resDeficitDiaEl.textContent = deficitAbs.toFixed(0) + " kcal · Superávit estimado hoje";
            resDeficitExtraEl.textContent = "Você ingeriu aproximadamente " + caloriasConsumidas.toFixed(0) +
                " kcal. Em relação ao gasto estimado (" + Math.round(tdee) + " kcal), isso indica superávit.";
            var pill3 = document.createElement("span");
            pill3.className = "res-pill danger";
            pill3.textContent = "Superávit (tende ao armazenamento de energia)";
            resDeficitPillsEl.appendChild(pill3);
        } else {
            resDeficitDiaEl.textContent = "0 kcal · Equilíbrio energético aproximado";
            resDeficitExtraEl.textContent = "A ingestão diária está muito próxima do gasto estimado.";
            var pill4 = document.createElement("span");
            pill4.className = "res-pill";
            pill4.textContent = "Equilíbrio aproximado";
            resDeficitPillsEl.appendChild(pill4);
        }
    }

    var textoAlvoPadrao = Math.round(alvoModerado) + " kcal/dia · alvo moderado sugerido";
    var textoAlvoPadraoExtra = "Faixa de referência: ~" + Math.round(alvoLento) + " a " +
        Math.round(alvoMaisAgressivo) +
        " kcal/dia, dependendo de histórico clínico, bioimpedância e supervisão profissional.";

    var dadosMonjaro = obterDadosMonjaro();
    atualizarCardMonjaro(dadosMonjaro);

    var modoMonjaroSelect = document.getElementById("monjaro-modo");
    var modoMonjaro = modoMonjaroSelect ? modoMonjaroSelect.value : "A";

    if (!dadosMonjaro.ativo || !dadosMonjaro.deficitDiaKcal || dadosMonjaro.deficitDiaKcal <= 0) {
        resDeficitSugEl.textContent = textoAlvoPadrao;
        resDeficitSugExtraEl.textContent = textoAlvoPadraoExtra;
    } else {
        var deficitDiaMonjaro = dadosMonjaro.deficitDiaKcal;
        var alvoBaseMonjaro = Math.max(tdee - deficitDiaMonjaro, minKcal);

        if (modoMonjaro === "A") {
            resDeficitSugEl.textContent = textoAlvoPadrao;
            resDeficitSugExtraEl.textContent =
                textoAlvoPadraoExtra +
                " Modo A · Apenas informativo. Sua meta diária continua baseada no seu TDEE. " +
                "O Monjaro está estimado em: " +
                dadosMonjaro.perdaSemKg.toFixed(2) +
                " kg/sem (equivalente a " +
                Math.round(dadosMonjaro.deficitDiaKcal) +
                " kcal/dia).";
        } else if (modoMonjaro === "B") {
            resDeficitSugEl.textContent =
                Math.round(alvoModerado) + " kcal/dia · meta recomendada";

            resDeficitSugExtraEl.textContent =
                "Modo B · Sugestão utilizando a perda típica da dose (" +
                dadosMonjaro.perdaSemKg.toFixed(2) + " kg/sem). " +
                "Para atingir essa perda, o déficit equivalente seria " +
                Math.round(deficitDiaMonjaro) + " kcal/dia, o que corresponde " +
                "a uma ingestão aproximada de " +
                Math.round(alvoBaseMonjaro) + " kcal/dia. " +
                "Use como referência para ajustar sua meta dentro de uma faixa segura.";
        } else if (modoMonjaro === "C") {
            resDeficitSugEl.textContent =
                Math.round(alvoBaseMonjaro) +
                " kcal/dia · meta ajustada automaticamente";

            resDeficitSugExtraEl.textContent =
                "Modo C · Integração completa. A meta foi ajustada para refletir a perda estimada com a dose (" +
                dadosMonjaro.perdaSemKg.toFixed(2) + " kg/sem). " +
                "Isso exige um déficit aproximado de " +
                Math.round(deficitDiaMonjaro) + " kcal/dia. " +
                "O valor foi limitado para manter um mínimo calórico considerado mais seguro.";
        } else {
            resDeficitSugEl.textContent = textoAlvoPadrao;
            resDeficitSugExtraEl.textContent = textoAlvoPadraoExtra;
        }
    }

    resultadosEl.classList.add("show");
}

function inicializarMonjaroToggle() {
    var checkbox = document.getElementById("usa-monjarocheck");
    var painel = document.getElementById("monjaro-panel");

    if (!checkbox || !painel) {
        return;
    }

    checkbox.addEventListener("change", function () {
        if (checkbox.checked) {
            painel.classList.add("open");
        } else {
            painel.classList.remove("open");
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    inicializarMonjaroToggle();
    var form = document.getElementById("form-deficit");
    if (form) {
        form.addEventListener("submit", calcularDeficit);
    }
});
