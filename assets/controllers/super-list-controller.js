import { Controller } from "@hotwired/stimulus";
import { delay, max } from "underscore";
import { CacheObject, navigateObject } from "../classes/tools";
import "../styles/controllers/superList.scss";
import { SCElement, SCHandler } from "./shortcut-controller";

export default class extends Controller {
    static values = {
        url: String,
        token: String,
        columnas: Object,
        classes: {type: Array, default: ["centered"]},
        noResultsMessage: {type: String, default: "No se encontraron resultados"},
        showOptions: {type: Array, default: [10, 20, 50, 100, 200, 500, 1000]},
        show: {type: Number, default: 10},
        currentPage: {type: Number, default: 1},
        total: {type: Number, default: 0},
        paginationMidRange: {type: Number, default: 5},
        totalPages: {type: Number, default: 1}
    }
    initialize(){
        this.element.classList.add("super-list")
        this.checkRequired()
        this.constructTable();
        window.onhashchange = this.hashChange.bind(this);
        this.hashChange();
        this.row = window.row;
        if (!this.row){
            this.row = (tr, row) => {}
        }
        this.cell = window.cell;
        if (!this.cell){
            this.cell = (td, col, row) => {
                return navigateObject(row, col);
            }
        }
        window.addEventListener("shortcuts-enabled", this.shortcuts.bind(this));
        /** @type {CacheObject} */
        this.cache = new CacheObject("super-table" + window.location.pathname, {
            show: this.showValue,
            currentPage: this.currentPageValue
        });
        this.restore();
    }
    restore(){
        this.updateCurrentPage = false;
        this.showValue = this.cache.getItem("show");
        this.currentPageValue = this.cache.getItem("currentPage");
    }
    /**
     * 
     * @param {CustomEvent} ev
     */
    shortcuts(ev) {
        /** @type {SCHandler} */
        let sch = ev.detail;
        sch.addElement(new SCElement({
            keyComb: "ctrl+alt+right",
            description: "Navega a la página siguiente de la tabla",
            callback: () => {
                window.location.hash = "#next";
            }
        }));
        sch.addElement(new SCElement({
            keyComb: "ctrl+alt+left",
            description: "Navega a la página anterior de la tabla",
            callback: () => {
                window.location.hash = "#prev";
            }
        }));
        sch.addElement(new SCElement({
            keyComb: "ctrl+alt+up",
            description: "Navega a la última página de la tabla",
            callback: () => {
                window.location.hash = "#last";
            }
        }));
        sch.addElement(new SCElement({
            keyComb: "ctrl+alt+down",
            description: "Navega a la primera página de la tabla",
            callback: () => {
                window.location.hash = "#first";
            }
        }));
    }
    /**
     * 
     * @param {HashChangeEvent} ev 
     */
    hashChange(ev){
        if (/#page_\d+/.test(window.location.hash)) {
            this.currentPageValue = window.location.hash.split("_").at(-1);
        }
        if (window.location.hash == "#first"){
            this.currentPageValue = 1;
        }
        if (window.location.hash == "#prev"){
            if (this.currentPageValue <= 1) {
                window.location.hash = "#page_1"
                return;
            }
            let temp = this.currentPageValue - 1;
            if (temp < 1) temp = 1;
            this.currentPageValue = temp;
        }
        if (window.location.hash == "#next"){
            if (this.currentPageValue >= this.totalPagesValue) {
                window.location.hash = "#page_" + this.totalPagesValue;
                return;
            }
            let temp = this.currentPageValue + 1;
            if (temp < 1) temp = 1;
            this.currentPageValue = temp;
        }
        if (window.location.hash == "#last"){
            this.currentPageValue = this.totalPagesValue;
        }
    }
    currentPageValueChanged() {
        let hash = "#page_" + this.currentPageValue;
        if (window.location.hash !== hash) window.location.hash = hash;
        this.cache.setItem("currentPage", this.currentPageValue);
        this.update();
        this.updatePagination();
    }
    updatePagination(){
        let minmax = this.getMinMax();
        this.element.querySelectorAll(".pagination li").forEach(li => {
            li.classList.remove("active");
            if (li.dataset.page == this.currentPageValue) li.classList.add("active");
            if (li.dataset.page < minmax.min || li.dataset.page > minmax.max) li.remove();
        });
        for (let i = minmax.min; i <= minmax.max; i++){
            let li = this.element.querySelector(`.pagination li[data-page='${i}']`);
            if (li !== null) continue;
            let next = this.element.querySelector(`.pagination li[data-page='${i+1}']`);
            let prev = this.element.querySelector(`.pagination li[data-page='${i-1}']`);
            if (next) {
                this.element.querySelector(".pagination").insertBefore(this.createPage(i), next);
                continue;
            }
            if (prev){
                this.element.querySelector(".pagination").insertBefore(this.createPage(i), prev.nextSibling)
                continue;
            }
            this.element.querySelector(".pagination").insertBefore(this.createPage(i), this.navigationNext); 
        }
        if (this.currentPageValue > 1){
            this.navigationFirst.classList.remove("disabled")
            this.navigationPrev.classList.remove("disabled")
        }else {
            this.navigationFirst.classList.add("disabled")
            this.navigationPrev.classList.add("disabled")
        }

        if (this.currentPageValue < this.totalPagesValue){
            this.navigationNext.classList.remove("disabled")
            this.navigationLast.classList.remove("disabled")
        }else {
            this.navigationNext.classList.add("disabled")
            this.navigationLast.classList.add("disabled")
        }
    }
    totalValueChanged(){
        this.totalIndicator.textContent = this.totalValue
        this.createPagination();
    }
    showValueChanged(){
        if (this.currentPageValue === 1) this.update();
        this.cache.setItem("show", this.showValue);
        this.showList.value = this.showValue;
        if (this.updateCurrentPage) this.currentPageValue = 1;
        this.createPagination();
        this.updateCurrentPage = true;
    }
    checkRequired(){
        if (!this.urlValue) throw new Error("url no especificada");
        if (!this.tokenValue) throw new Error("token no especificado");
        if (Object.keys(this.columnasValue).length == 0) throw new Error("columnas no especificadas");
    }
    constructTable(){
        this.table = document.createElement("table");
        this.table.classList.add(...this.classesValue)

        this.createHeader();
        this.createBody();
        this.createFooter();

        this.element.appendChild(this.table);
    }
    createHeader(){
        this.tableHeader = document.createElement("thead");
        this.tableHeader.innerHTML = /*html*/ `<tr></tr>`;
        for (const column in this.columnasValue) {
            let label = this.columnasValue[column];
            let th = document.createElement("th");
            th.dataset.columna = column;
            th.textContent = label;
            this.tableHeader.querySelector("tr").appendChild(th);
        }
        this.table.appendChild(this.tableHeader);
    }
    createBody(){
        this.tableBody = document.createElement("tbody");
        this.noResultRow = document.createElement("tr");
        this.noResultRow.id = "noResultRow";
        this.noResultRow.innerHTML = /*html*/ `
            <td colspan="${Object.keys(this.columnasValue).length}">${this.noResultsMessageValue}</td>
        `;
        this.tableBody.appendChild(this.noResultRow);
        this.table.appendChild(this.tableBody);
    }
    createFooter(){
        this.tfoot = document.createElement("tfoot");
        this.tfoot.innerHTML = /*html*/ `
            <tr>
                <td colspan="${Object.keys(this.columnasValue).length}">
                    <div class="footer">
                        <div class="input-field">
                            <select id="showList" class="browser-default">
                                ${this.showOptionsValue.reduce((a, el) => a += `<option value="${el}">${el}</option>`, "")}
                            </select>
                        </div>
                        <div class="indicator">
                            de <span id="totalIndicator">?</span>
                        </div>
                        <ul class="pagination right-align">
                        
                        </ul>
                    </div>
                </td>
            </tr>
        `;
        this.createPagination();
        this.showList = this.tfoot.querySelector("#showList");
        this.showList.onchange = (ev) => {
            this.showValue = this.showList.value;
        }
        this.totalIndicator = this.tfoot.querySelector("#totalIndicator");
        this.table.appendChild(this.tfoot);
    }
    createPagination(){
        this.tfoot.querySelector(".pagination").innerHTML = "";
        let localTotal = this.totalValue == 0 ? 1 : this.totalValue;
        this.totalPagesValue = Math.ceil(localTotal / this.showValue);
        

        this.navigationFirst = document.createElement("li");
        this.navigationFirst.classList.add("waves-effect");
        this.navigationFirst.innerHTML = /*html*/ `<a href="#first"><i class="material-icons">first_page</i></a>`;
        if (this.currentPageValue <= 1) this.navigationFirst.classList.add("disabled");
        this.tfoot.querySelector(".pagination").appendChild(this.navigationFirst);

        this.navigationPrev = document.createElement("li");
        this.navigationPrev.classList.add("waves-effect");
        this.navigationPrev.innerHTML = /*html*/ `<a href="#prev"><i class="material-icons">chevron_left</i></a>`;
        if (this.currentPageValue <= 1) this.navigationPrev.classList.add("disabled");
        this.tfoot.querySelector(".pagination").appendChild(this.navigationPrev);
        
        let minmax = this.getMinMax();
        for (let i = minmax.min; i <= minmax.max; i++){
            this.tfoot.querySelector(".pagination").appendChild(this.createPage(i));
        }

        this.navigationNext = document.createElement("li");
        this.navigationNext.classList.add("waves-effect");
        this.navigationNext.innerHTML = /*html*/ `<a href="#next"><i class="material-icons">chevron_right</i></a>`;
        if (this.currentPageValue >= this.totalPagesValue) this.navigationNext.classList.add("disabled");
        this.tfoot.querySelector(".pagination").appendChild(this.navigationNext);

        this.navigationLast = document.createElement("li");
        this.navigationLast.classList.add("waves-effect");
        this.navigationLast.innerHTML = /*html*/ `<a href="#last"><i class="material-icons">last_page</i></a>`;
        if (this.currentPageValue >= this.totalPagesValue) this.navigationLast.classList.add("disabled");
        this.tfoot.querySelector(".pagination").appendChild(this.navigationLast);
    }
    createPage(i){
        let page = document.createElement("li");
        page.classList.add("waves-effect");
        page.innerHTML = /*html*/ `<a href="#page_${i}">${i}</a>`;
        page.dataset.page = i;
        if (this.currentPageValue == i) page.classList.add("active");
        return page;
    }
    getMinMax() {
        let min = this.currentPageValue - this.paginationMidRangeValue;
        let max = this.currentPageValue + this.paginationMidRangeValue;

        min = min < 1 ? 1 : min;
        max = max > this.totalPagesValue ? this.totalPagesValue : max;
        return {
            min: min,
            max: max
        }
    }
    async update(){
        this.getTotal();
        let fetchResponse = await fetch(this.getURL(), {
            headers: new Headers({
                "Authorization": `Bearer ${this.tokenValue}`
            })
        });
        if (fetchResponse.status != 200){
            console.error(fetchResponse);
            return;
        }
        let response = await fetchResponse.json();
        this.fillData(response.data);
    }
    async getTotal() {
        let fetchResponse = await fetch(this.getURL(true), {
            headers: new Headers({
                "Authorization": `Bearer ${this.tokenValue}`
            })
        });
        if (fetchResponse.status != 200){
            return;
        }
        let response = await fetchResponse.json();
        this.totalValue = response.data.at(0).total
    }
    /**
     * 
     * @param {Array} data 
     */
    fillData(data){
        if (data.length > 0){
            this.noResultRow.classList.add("hide");
        }else {
            this.noResultRow.classList.remove("hide");
        }
        this.tableBody.querySelectorAll("tr:not(#noResultRow)").forEach(el => {
            el.remove();
        })
        let delay = 0;
        data.forEach(rowData => {
            let tr = document.createElement("tr");
            tr.style.setProperty("--delay", delay + "ms");
            delay += 30;
            this.row(tr, rowData);
            for (let col of Object.keys(this.columnasValue)){
                let td = document.createElement("td");
                td.innerHTML = this.cell(td, col, rowData);
                tr.appendChild(td);
            }
            this.tableBody.appendChild(tr);
        });

    }
    getURL(total=false){
        let url = new URL(window.location.origin + this.urlValue);
        if (total) url.searchParams.append("getTotal", true);
        url.searchParams.append("show", this.showValue);
        url.searchParams.append("offset", this.showValue * (this.currentPageValue - 1));
        return url;
    }
}