import { Controller } from "@hotwired/stimulus";
import "../styles/controllers/tooltipped.scss";

export default class extends Controller {
    initialize(){
        this.element.classList.add("tooltipped");
        M.Tooltip.init(this.element, {});
    }
}