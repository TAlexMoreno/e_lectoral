import { Controller } from "@hotwired/stimulus";
import "../styles/controllers/sidebar.scss"
import { SCElement, SCHandler } from "./shortcut-controller";

export default class extends Controller {
    static values = {
        collapsed: {type: Boolean, default: false}
    }
    initialize(){
        document.querySelector("main").classList.add("sidebar")
        this.collapsedValue = localStorage.getItem("axs-sidebar-collapsed") == "true";
        this.element.classList.add("axs-sidebar");
        this.element.innerHTML = /*html*/`
            <div class="header">
                <div id="brand-name"><img src="/img/white_logo_small.png" alt="logo"><span>Grand Ticket</span></div>
                <button id="collapse-button" class="btn-flat btn-small"><i class="material-icons">arrow_back_ios</i></button>
            </div>
            <div class="menus"></div>
        `;
        this.collapseButton = this.element.querySelector("#collapse-button");
        this.collapseButton.onclick = () => this.toggle();

        this.getRutas().then(menus => {
            menus.forEach(menu => {
                let menuElement = document.createElement("div");
                menuElement.classList.add("item");
                menuElement.innerHTML = /*html*/`
                    <i class="material-icons icon">${menu.icon}</i>
                    <div class="label">${menu.label}</div>
                `;
                menuElement.onclick = () => {
                    window.location.href = menu.path;
                };
                this.element.querySelector(".menus").appendChild(menuElement);
            })
        });

        window.addEventListener("toggle-sidebar", this.toggle.bind(this));
        window.addEventListener("shortcuts-enabled", this.addShortcuts.bind(this));
    }
    /**
     * 
     * @param {CustomEvent} ev
     */
    addShortcuts(ev){
        /** @type {SCHandler} */
        this.sch = ev.detail;
        this.sch.addElement(new SCElement({
            keyComb: "ctrl+alt+b",
            description: "Alterna la barra de navegación",
            callback: () => {
                this.toggle();
            }
        }));
    
        
    }
    toggle(){
        this.collapsedValue = !this.collapsedValue;
        localStorage.setItem("axs-sidebar-collapsed", this.collapsedValue ? "true" : "false")
    }
    collapsedValueChanged(){
        if (this.collapsedValue){
            this.element.classList.add("collapsed");
            document.querySelector("main").classList.add("collapsed");
            this.collapseButton.querySelector("i").innerHTML = "arrow_forward_ios";
        }else {
            this.element.classList.remove("collapsed");
            document.querySelector("main").classList.remove("collapsed");
            this.collapseButton.querySelector("i").innerHTML = "arrow_back_ios";
        }
    }
    /**
     * Hace fetch para regresar los menus permitidos
     * @returns {Promise<Array<MenuItem>>} Menus
     */
    async getRutas(){
        let fetchResponse = await fetch("/getRutas", {
            method: "POST"
        });
        //TODO manejar error del response;
        let response = await fetchResponse.json();
        if (!response.success) return null;
        return response.rutas; 
    }
}
class MenuItem {
    constructor(){
        this.path = "";
        this.label = "";
        this.icon = "";
        this.order = 0;
    }
}