export let navigateObject = (object, path) => {
    path = path.split(".");
    for (const prop of path) {
        object = object[path];
    }
    return object;
}

export class CacheObject {
    constructor(path="", properties={}){
        this.path = path;
        this.defaults = properties;
        this.restore();
    }

    restore(){
        this.properties = JSON.parse(localStorage.getItem(this.path));
        if (!this.properties) {
            this.properties = {};
            for (const key in this.defaults) {
                this.properties[key] = this.defaults[key];
            }
            this.save();
        }
    }

    save() {
        localStorage.setItem(this.path, JSON.stringify(this.properties));
    }

    getItem(name){
        if (!(name in this.properties)) throw new Error(`No existe la propiedad ${name} en ${this.path}`);
        return this.properties[name];
    }

    setItem(name, value){
        if (!(name in this.properties)) throw new Error(`No existe la propiedad ${name} en ${this.path}`);
        this.properties[name] = value;
        this.save();
    }
}

window.navigateObject = navigateObject;