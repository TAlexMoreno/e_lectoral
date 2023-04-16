import { Controller } from "@hotwired/stimulus";
import M from "@materializecss/materialize";
import hotkeys from "hotkeys-js";
import { delay } from "underscore";
import "../styles/controllers/shortcut.scss"

export default class extends Controller {
    initialize() {
        this.selected = 0;
        this.modalElement = document.createElement("div")
        this.modalElement.classList.add("modal")
        this.modalElement.id = "shortcutPanel";
        this.modalElement.innerHTML = /*html*/`
            <nav>
                <div class="nav-wrapper">
                    <div class="search"><input type="text" class="browser-default" id="searchCommand"></div>
                </div>
            </nav>
            <div class="modal-content"></div>
        `;
        this.modal = M.Modal.init(this.modalElement, {
            onOpenEnd: () => {
                this.modalElement.querySelector("#searchCommand").focus();
            }
        });
        document.body.appendChild(this.modalElement);

        this.scHandler = new SCHandler(this.updateCommands.bind(this));
        this.scHandler.addElement(new SCElement({
            keyComb: "alt+p",
            callback: () => {
                this.modal.open();
            },
            description: "Abre el panel de atajos",
            showInPanel: false
        }))


        delay(() => {
            window.dispatchEvent(new CustomEvent("shortcuts-enabled", {
                detail: this.scHandler
            }));
        }, 250);

        this.search = this.modalElement.querySelector("input");
        this.search.onkeydown = this.keydown.bind(this);
        this.search.addEventListener("focusout", () => delay(() => this.modal.close(), 100));
    }
    /**
     * 
     * @param {KeyboardEvent} ev 
     */
    keydown(ev){
        if (ev.key == "ArrowDown"){
            ev.preventDefault();
            this.next();
            return;
        }
        if (ev.key == "ArrowUp"){
            ev.preventDefault();
            this.prev();
            return;
        }
        if (ev.key == "Enter"){
            ev.preventDefault();
            this.execute();
            return;
        }

        this.updateCommands();

    }
    next(){
        let current = this.modalElement.querySelector(".shortcut.active")
        let next = current.nextSibling;
        if (!next) next = current.parentNode.firstChild;
        current.classList.remove("active");
        next.classList.add("active"); 
    }
    prev(){
        let current = this.modalElement.querySelector(".shortcut.active")
        let prev = current.previousSibling;
        if (!prev) prev = current.parentNode.lastChild
        current.classList.remove("active");
        prev.classList.add("active"); 
    }
    execute(){
        let search = this.scHandler.elements.filter(
            el => el.keyComb == this.modalElement.querySelector(`.shortcut.active`).dataset.keyComb
        )
        let result = search.at(0).callback();
        if (!result) this.modal.close();
    }
    updateCommands(){
        this.modalElement.querySelector(".modal-content").innerHTML = "";
        let count = -1;
        for (const sc of this.scHandler.elements) {
            if (!sc.showInPanel) continue;
            if (this.search != "" && sc.description.search(this.search.value) == -1) continue;
            count += 1;
            let el = document.createElement("div")
            el.dataset.keyComb = sc.keyComb;
            if (count == 0) el.classList.add("active")
            el.classList.add("shortcut")
            el.innerHTML = /*html*/ `
                <div class="description">${sc.description}</div>
                <div class="keycomb">${sc.keyComb}</div>
            `;
            this.modalElement.querySelector(".modal-content").appendChild(el);
            el.onclick = sc.callback;
        }
    }
}

export class SCHandler {
    constructor(afterAdd=()=>{}){
        /** @type {Array<SCElement>} */
        this.elements = [];
        this.afterAdd = afterAdd;
    }
    /**
     * 
     * @param {SCElement} sc 
     */
    addElement(sc){
        this.elements.unshift(sc);
        if (sc.keyComb !== "" && sc.keyComb !== null) hotkeys(sc.keyComb, sc.callback);
        this.afterAdd();
    }
}

export class SCElement {
    constructor(opt={
        keyComb: "",
        callback: ()=>{console.log("callback");},
        description: "",
        showInPanel: true
    }){
        /** @type {string} */
        this.keyComb = opt.keyComb;
        this.callback = opt.callback;
        this.description = opt.description;
        this.showInPanel = "showInPanel" in opt ? opt.showInPanel : true;
    }
}