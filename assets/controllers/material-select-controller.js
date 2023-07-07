import { Controller } from "@hotwired/stimulus";
import M from "@materializecss/materialize";

export default class extends Controller {
    initialize() {
        M.FormSelect.init(this.element.querySelector("select"), {dropdownOptions: {container: document.body}});
    }
}