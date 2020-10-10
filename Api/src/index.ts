import './config/environment.init'; // Needs to be imported first, contains environment variable
import express from 'express';
import * as bodyParser from 'body-parser';
import {Routes} from './config/routes';
import {logger} from './config/logger';
import swaggerUi from 'swagger-ui-express';
import swaggerDocument from './config/swagger';

const PORT = process.env.SERVER_PORT;

class Index {
    public app: express.Application;
    public routePrv: Routes = new Routes();

    constructor() {
        this.app = express();
        this.config();
        this.routePrv.routes(this.app);
    }

    private config(): void {
        this.app.use(bodyParser.json());
        this.app.use(bodyParser.urlencoded({extended: false}));
        if (process.env.NODE_ENV === 'development') {
            this.configDev();
        }
    }

    private configDev(): void {
        this.app.use(
            '/swagger',
            swaggerUi.serve,
            swaggerUi.setup(swaggerDocument)
        );
    }
}

export default new Index().app.listen(PORT, () =>
    logger.info(`Application listening on port ${PORT}!`)
);
