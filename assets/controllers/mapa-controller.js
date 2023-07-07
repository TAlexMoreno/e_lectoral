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

export default class extends Controller {
    async initialize(){
        this.distritos = [];
        useGeographic();
        this.element.classList.add('mapa-container');
        this.estadoLayer = new VectorLayer({
            source: new VectorSource({
                format: new GeoJSON(),
                url: "/test/geojson/seccional/26"
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
                console.log(feature.get("MUNICIPIO"));
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
        let municipio = feature.get("MUNICIPIO");
        if (!this.distritos.includes(municipio)) {
            this.distritos.push(municipio);
            console.log(municipio);
        };
        let color = colors[this.distritos.indexOf(municipio) % colors.length];
        return new Style({
            fill: new Fill({ color: color.light }),
            stroke: new Stroke({ color: color.dark, width: 1 }),
            text: new Text({
                font: "12px Calibri,sans-serif",
                fill: new Fill({ color: "#000" }),
                stroke: new Stroke({ color: "#fff", width: 1 }),
                text: (feature.get("NOMBRE") ?? feature.get("SECCION")).toString()
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
                text: (feature.get("NOMBRE") ?? feature.get("SECCION")).toString()
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
                text: (feature.get("NOMBRE") ?? feature.get("SECCION")).toString()
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
