import {Request, Response} from 'express';
import {Player} from '../models/player.model';
import PlayerService from '../services/player.service';
import DaedalusService from '../services/daedalus.service';
import {Daedalus} from '../models/daedalus.model';
import {validationResult} from 'express-validator';
import {logger} from '../config/logger';

export class PlayerController {
    public fetch(req: Request, res: Response): void {
        const identifier = Number(req.params.id);

        PlayerService.find(identifier)
            .then((player: Player | null) => {
                if (player === null) {
                    res.status(404).json();
                }
                res.json(player);
            })
            .catch((err: Error) => {
                res.status(500).json(err);
            });
    }

    public fetchAll(req: Request, res: Response): void {
        PlayerService.findAll()
            .then((players: Player[]) => {
                res.json(players);
            })
            .catch((err: Error) => {
                res.status(500).json(err);
            });
    }

    public post(req: Request, res: Response): void {
        const character = req.body.character;

        const errors = validationResult(req); // Finds the validation errors in this request and wraps them in an object with handy functions

        if (!errors.isEmpty()) {
            res.status(422).json({errors: errors.array()});
            return;
        }

        DaedalusService.find(req.body.daedalus)
            .then((daedalus: Daedalus | null) => {
                if (daedalus === null) {
                    res.status(422).json(
                        'Invalid Daedalus identifier provided : ' +
                            req.body.daedalus
                    );
                    return;
                }

                PlayerService.initPlayer(daedalus, character)
                    .then((player: Player) => {
                        res.status(201).json(player);
                    })
                    .catch((err: Error) => {
                        logger.error(err.message);
                        res.status(500).json(err);
                    });
            })
            .catch((err: Error) => {
                logger.error(err.message);
                res.status(500).json(err);
            });
    }

    public put(req: Request, res: Response): void {
        res.status(501).send('Method not implemented!');
    }

    public patch(req: Request, res: Response): void {
        res.status(501).send('Method not implemented!');
    }
    public delete(req: Request, res: Response): void {
        res.status(501).send('Method not implemented!');
    }
}
