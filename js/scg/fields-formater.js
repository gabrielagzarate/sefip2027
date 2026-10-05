window.PolFizaFormatter = function (a, t) { return "ORD" != t ? a : a + "<img src='css/images/punto.png'>" }
window.FormatoMoneda = function (a, t) {
    let strIni = "";
    let strFin = "";

    // Si hay diferencia entre 'a' y 't', se marca en rojo con parpadeo
    if (a != t) {
        strIni = '<span class="parpadea" title="Hay diferencia entre la suma del Debe y del Haber"><font color="#FF0000"><b>';
        strFin = "</b></font></span>";
    }

    // Si el valor es nulo, vacío o cero, no se muestra nada
    if (a === null || a === "" || a == 0) return "- - -";

    let r = a;
    let isNegative = r < 0;
    let s = isNegative ? "-" : "";

    // Redondear a 2 decimales y tomar valor absoluto
    r = Math.abs(+r || 0).toFixed(2);

    // Parte entera y separadores de miles
    let i = parseInt(r) + "";
    let j = i.length > 3 ? i.length % 3 : 0;

    // Armar el número formateado
    let formattedNumber =
        s +
        (j ? i.substr(0, j) + "," : "") +
        i.substr(j).replace(/(\d{3})(?=\d)/g, "$1,") +
        "." +
        Math.abs(r - i).toFixed(2).slice(2);

    // Color rojo si es negativo, negro si no
    let colorStyle = isNegative ? "red" : "black";
    formattedNumber = `<span style="color: ${colorStyle};">${formattedNumber}</span>`;

    // Devolver con advertencia visual si aplica
    return strIni + formattedNumber + strFin;
};
window.LDFormatoMoneda = function(a, t) {
    let strIni = "",
        strFin = "";

    if (a === null || a === "" || a == 0) return "";

    let r = a;
    let s = r < 0 ? "-" : "";
    let absValue = Math.abs(+r || 0).toFixed(2);
    let i = parseInt(absValue).toString();
    let decimal = absValue.split(".")[1];

    // Separamos la longitud antes de calcular j
    let len = i.length;
    let j = len > 3 ? len % 3 : 0;

    let formattedNumberN =
        (j ? i.substr(0, j) + "," : "") +
        i.substr(j).replace(/(\d{3})(?=\d)/g, "$1,") +
        "." + decimal;

    return strIni + s + formattedNumberN + strFin;
};




