let segundos = 0;
let intervalo = null;

function actualizarCronometro() {
    let hrs = Math.floor(segundos / 3600);
    let mins = Math.floor((segundos % 3600) / 60);
    let secs = segundos % 60;

    let tiempo = `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    document.getElementById('cronometro').innerText = tiempo;
    document.getElementById('tiempo').value = tiempo;
}

function iniciarCronometro() {
    if (intervalo) return;
    intervalo = setInterval(() => {
        segundos++;
        actualizarCronometro();
    }, 1000);
}

function pararCronometro() {
    clearInterval(intervalo);
    intervalo = null;
}

function reiniciarTabla() {
    if (!confirm("¿Estás seguro de que quieres reiniciar la tabla?")) return;

    jQuery.post(tr_ajax_obj.ajax_url, {
        action: 'reiniciar_tabla'
    }, function(response) {
        if (response.success) {
            alert("¡Tabla reiniciada!");
            location.reload();
        } else {
            alert("Error al reiniciar: " + response.data);
        }
    });
}
