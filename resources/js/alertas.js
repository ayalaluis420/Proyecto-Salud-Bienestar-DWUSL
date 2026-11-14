import Swal from 'sweetalert2';

// Delegation also covers forms introduced by future modules.
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    const deletes = form.querySelector('input[name="_method"]')?.value?.toUpperCase() === 'DELETE';
    if (!form.dataset.confirm && !deletes) return;
    event.preventDefault();
    const submitter = event.submitter;
    const result = await Swal.fire({
        title: 'Confirmar acción', text: form.dataset.confirm || '¿Eliminar este registro? Esta acción no se puede deshacer.',
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, continuar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#c0392b', focusCancel: true,
    });
    if (result.isConfirmed && form.isConnected) {
        if (submitter?.name) {
            const input = document.createElement('input'); input.type = 'hidden'; input.name = submitter.name; input.value = submitter.value; form.appendChild(input);
        }
        HTMLFormElement.prototype.submit.call(form);
    }
});

async function showMessages() {
    for (const element of document.querySelectorAll('[data-panel-alert]')) {
        await Swal.fire({ icon: element.dataset.panelAlert, title: element.dataset.title, text: element.textContent.trim(), confirmButtonText: 'Aceptar' });
        element.remove();
    }
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', showMessages, { once: true });
else showMessages();
