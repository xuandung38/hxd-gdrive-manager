import Vue from 'vue';
import ElementUI from 'element-ui';
import elementLocale from 'element-ui/lib/locale/lang/en';
import VueLodash from 'vue-lodash';
import lodash from 'lodash';
import VueInternationalization from 'vue-i18n';
import Locale from './i18n.js';
import { Request } from "./request";
import { Editor } from "./editor";

Vue.use(VueLodash, { lodash });
Vue.use(ElementUI, { locale: elementLocale });
Vue.use(VueInternationalization);
Vue.prototype.Request  = Request;
Vue.prototype.Editor  = Editor;

const components = require.context('./components', true, /\.vue$/i); // eslint-disable-line
components.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], components(key).default));

const i18n = new VueInternationalization({
    locale: document.head.querySelector('meta[name="locale"]').content,
    messages: Locale
});

new Vue({
    el: '#app',
    i18n
});
