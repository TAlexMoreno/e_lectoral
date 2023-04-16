import { Controller } from "@hotwired/stimulus";
import "../styles/controllers/niceLoadingCard.scss";


export default class extends Controller {
    initialize(){
        this.alarmed = false;
        this.element.classList.add("nice-loading-card");
        this.element.querySelector("form").addEventListener("submit", this.submit.bind(this));
        this.element.querySelector("form input:not(disabled):not([type='hidden'])").focus()
    }
    /**
     * 
     * @param {SubmitEvent} ev 
     */
    async submit(ev){
        ev.preventDefault();
        if (this.alarmed) return;
        this.element.querySelectorAll(".card-action button, .card-action a").forEach(btn => {
            btn.classList.add("animationTelevisionOff");
        });
        this.addLoader();
        let data = new FormData(ev.target);
        let fetchResponse = await fetch(ev.target.getAttribute("action"), {
            method: ev.target.getAttribute("method"),
            body: data
        });
        let response = fetchResponse.status < 500 ? await fetchResponse.json() : {
            "errno": fetchResponse.status,
            "error": fetchResponse.statusText
        };
        if (fetchResponse.status != 200){
            this.setError(response);
        } else {
            if (response.action == "redirect"){
                window.location.href = response.path;
            }else {
                this.setMessage(response)
            }
        }
        this.loader.remove();
        this.loader = null;
    }
    setError(response){
        this.alarmed = true;
        let error = document.createElement("div");
        error.classList.add("error");
        error.innerHTML = /*html*/ `
            <span class="red-text text-lighten-2">${response.error} (${response.errno})</span>
            <button title="Ignorar" class="btn-small red">ok</button>
        `;
        error.querySelector("button").onclick = () => {
            error.classList.add("animationTelevisionOff")
        }
        error.onanimationend = () => {
            this.alarmed = false;
            error.remove();
            this.element.querySelectorAll(".card-action button, .card-action a").forEach(btn => {
                btn.classList.remove("animationTelevisionOff");
                btn.classList.add("animationTelevisionOn");
                btn.onanimationend = () => { 
                    btn.classList.remove("animationTelevisionOn"); 
                }
            })
        }
        this.element.querySelector(".card-action").appendChild(error);
        error.querySelector("button").focus();
    }
    setMessage(response){
        this.alarmed = true;
        let message = document.createElement("div");
        message.classList.add("error");
        message.innerHTML = /*html*/ `
            <span class="orange-text text-lighten-2">${response.message}</span>
            <button title="Ignorar" class="btn-small red">ok</button>
        `;
        message.querySelector("button").onclick = () => {
            message.classList.add("animationTelevisionOff")
        }
        message.onanimationend = () => {
            this.alarmed = false;
            message.remove();
            this.element.querySelectorAll(".card-action button, .card-action a").forEach(btn => {
                btn.classList.remove("animationTelevisionOff");
                btn.classList.add("animationTelevisionOn");
                btn.onanimationend = () => { 
                    btn.classList.remove("animationTelevisionOn"); 
                }
            })
        }
        this.element.querySelector(".card-action").appendChild(message);
        message.querySelector("button").focus();
    }
    addLoader(){
        this.loader = document.createElement("div");
        this.loader.classList.add("progress");
        this.loader.innerHTML = /*html*/`
            <div class="indeterminate"></div>
        `;
        this.loader.classList.add("animationTelevisionOn")
        this.element.querySelector(".card-action").appendChild(this.loader);
    }
}