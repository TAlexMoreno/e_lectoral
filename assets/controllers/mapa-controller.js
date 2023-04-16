import { Controller } from "@hotwired/stimulus";
import { Map, View } from "ol";
import "../styles/controllers/mapa.scss";
import TileLayer from "ol/layer/Tile";
import TileWMS from "ol/source/TileWMS";
import { useGeographic } from "ol/proj";
import VectorLayer from "ol/layer/Vector";
import VectorSource from "ol/source/Vector";
import GeoJSON from "ol/format/GeoJSON";
import Style from "ol/style/Style";
import Text from "ol/style/Text";
import Fill from "ol/style/Fill";
import Stroke from "ol/style/Stroke";

const colors = [
    {light: "#ef9a9a", dark: "#b71c1c"},
    {light: "#f48fb1", dark: "#880e4f"},
    {light: "#ce93d8", dark: "#4a148c"},
    {light: "#b39ddb", dark: "#311b92"},
    {light: "#9fa8da", dark: "#1a237e"},
    {light: "#90caf9", dark: "#0d47a1"},
    {light: "#81d4fa", dark: "#01579b"},
    {light: "#80deea", dark: "#006064"},
    {light: "#80cbc4", dark: "#004d40"},
    {light: "#a5d6a7", dark: "#1b5e20"},
    {light: "#c5e1a5", dark: "#33691e"},
    {light: "#e6ee9c", dark: "#827717"},
    {light: "#fff59d", dark: "#f57f17"},
    {light: "#ffe082", dark: "#ff6f00"},
    {light: "#ffcc80", dark: "#e65100"},
    {light: "#ffab91", dark: "#bf360c"},
    {light: "#bcaaa4", dark: "#3e2723"},
    {light: "#eeeeee", dark: "#212121"},
    {light: "#b0bec5", dark: "#263238"},
]

const distritos = {
    "01": ["006", "020", "021", "060"],
    "02": ["011", "034", "043"],
    "03": ["004", "030", "031", "039", "055", "054"],
    "04": ["003"],
    "05": ["001", "002", "024", "036", "040"],
    "06": ["012", "014", "015", "019", "020", "055"],
    "07": ["033"],
    "08": ["009", "018", "026", "038", "050"],
    "09": ["010", "052"],
    "10": ["013", "016", "035", "037"],
    "11": ["005", "007", "008", "013"],
    "12": ["049", "032", "051", "053", "029", "060", "022", "028", "051"],
    "13": ["056", "023", "057", "044", "054"],
    "14": ["042", "058", "059", "041", "017", "027"],
    "15": ["025"]
}

export default class extends Controller {
    initialize(){
        useGeographic();
        this.element.classList.add('mapa-container');
        this.estadoLayer = new VectorLayer({
            source: new VectorSource({
                format: new GeoJSON(),
                url: "https://gaia.inegi.org.mx/wscatgeo/geo/mgem/29"
            }),
            style: (feature) => {
                return this.baseStyle(feature);
            }
        });
        this.mapa = new Map({
            target: this.element,
            view: new View({
                center: [-98.18044778793872, 19.456391634249297],
                zoom: 10,
            }),
            layers: [
                this.estadoLayer
            ]
        });
        this.firstRender = false;
        this.mapa.on("rendercomplete", () => {
            if (!this.firstRender){
                this.mapa.getView().fit(this.estadoLayer.getSource().getExtent());
                this.firstRender = true;
            }
        })
        this.hovered = null;
        this.mapa.on("pointermove", (e) => {
            if (this.hovered !== null && this.selected?.get("cvegeo") !== this.hovered.get("cvegeo")){
                this.hovered.setStyle(this.baseStyle(this.hovered));
                this.hovered = null;
            }
            this.mapa.forEachFeatureAtPixel(e.pixel, (feature) => {
                if (this.selected?.get("cvegeo") == feature.get("cvegeo")) return;
                this.hovered = feature;
                this.hovered.setStyle(this.hoverStyle(feature));
                return true;
            });
        });
        this.selected = null;
        this.mapa.on("click", (e) => {
            if (this.selected !== null) {
                this.selected.setStyle(this.baseStyle(this.selected));
                this.selected = null;
            }
            this.mapa.forEachFeatureAtPixel(e.pixel, (feature) => {
                this.selected = feature;
                this.selected.setStyle(this.selectedStyle(feature));
                let geometry = this.selected.getGeometry();
                this.mapa.getView().fit(geometry, {
                    padding: [10, this.element.clientWidth/2, 10, 10],
                    duration: 500
                });
                return true;
            });
        })
    }
    /**
     * 
     * @param {import("ol/Feature").FeatureLike} feature 
     */
    baseStyle(feature){
        console.log(feature.get("cve_agem"));
        let distrito = this.findDistrito(feature.get("cve_agem"));
        let color = colors[Number(distrito)];
        return new Style({
            fill: new Fill({ color: color.light }),
            stroke: new Stroke({ color: color.dark, width: 1 }),
            text: new Text({
                font: "12px Calibri,sans-serif",
                fill: new Fill({ color: "#000" }),
                stroke: new Stroke({ color: "#fff", width: 1 }),
                text: feature.get("nom_agem") + "\n" + feature.get("cve_agem")
            })
        });
    }
    /**
     * 
     * @param {import("ol/Feature").FeatureLike} feature 
     */
    hoverStyle(feature){
        return new Style({
            fill: new Fill({ color: "rgba(160, 160, 160, 0.6)" }),
            stroke: new Stroke({ color: "#319FD3", width: 1 }),
            text: new Text({
                font: "12px Calibri,sans-serif",
                fill: new Fill({ color: "#000" }),
                stroke: new Stroke({ color: "#fff", width: 1 }),
                text: feature.get("nom_agem") + "\n" + feature.get("cve_agem")
            })
        });
    }
    /**
     * 
     * @param {import("ol/Feature").FeatureLike} feature 
     */
    selectedStyle(feature){
        return new Style({
            fill: new Fill({ color: "#00897b" }),
            stroke: new Stroke({ color: "#004d40", width: 1 }),
            text: new Text({
                font: "12px Calibri,sans-serif",
                fill: new Fill({ color: "#a7ffeb" }),
                stroke: new Stroke({ color: "#00bfa5", width: 1 }),
                text: feature.get("nom_agem") + "\n" + feature.get("cve_agem")
            })
        });
    }
    findDistrito(cve_agem){
        for (const distrito in distritos) {
            let municipios = distritos[distrito]
            if (municipios.indexOf(cve_agem) >= 0) return distrito;
        }
        return "000";
    }
}
