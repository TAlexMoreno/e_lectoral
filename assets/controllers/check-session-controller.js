import { Controller } from "@hotwired/stimulus";
import M from "@materializecss/materialize";

export default class extends Controller {
    initialize() {
        this.interval = setInterval(this.check.bind(this), 20000);
    }
    async check() {
        let response = await fetch("/checkSession", {method: "POST"});
        if (!response.ok) return;
        let data = await response.json();
        if (data.session) return;
        clearInterval(this.interval);
        this.modal = document.createElement("div");
        this.modal.classList.add("modal");
        this.modal.innerHTML = /*html*/ `
            <div class="modal-content">
                <h4>Tu sesión ha expirado</h4>
                <p>Has dejado pasar mucho tiempo sin realizar alguna acción en el sistema o tu sesión ha sido cerrada de forma remota por algún administrador. El sistema tratara de guardar cualquier tipo de información que no haya sido guardada.</p>
            </div>
            <div class="modal-footer right-align">
                <button id="recargar" class="btn">Ir a inicio de sesión</button>
            </div>
        `;
        document.body.appendChild(this.modal);
        this.modal.querySelector("#recargar").onclick = () => window.location.reload();
        let modal = M.Modal.init(this.modal, {dismissible: false});
        modal.open();
    }
}