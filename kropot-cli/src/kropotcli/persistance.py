from sqlalchemy import create_engine, Column, Integer, String, DateTime, Boolean, JSON
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
from sqlalchemy.orm.session import Session
from sqlalchemy.engine.base import Engine
from sqlalchemy.orm.exc import NoResultFound as ORMNoResultFound
from sqlalchemy import func
from datetime import datetime
from .logger import Logger
Base = declarative_base()


class ORM:
    engine: Engine
    session: Session  # type: Session

    def __init__(self, db_string: str):
        Logger.info('ORM is initializing')
        self.engine = create_engine(db_string, echo=True)
        self.session = sessionmaker(bind=self.engine, autoflush=False, autocommit=True)()
        Base.metadata.create_all(self.engine)


class ProcessedElementLog(Base):
    STATUS_IN_PROGRESS = 'in-progress'
    STATUS_NOT_TAKEN   = ''
    STATUS_DONE        = 'done'

    """
    Model for a history of all processed/in-progress items
    """

    __tablename__ = 'riotkit_kropotcli_processed_element_log'

    id = Column(Integer, primary_key=True, autoincrement=True)
    element_id = Column(String, nullable=False)
    element_type = Column(String, nullable=False)
    element_date = Column(DateTime, nullable=False)
    element_tz = Column(String, nullable=False)
    processed_at = Column(DateTime, nullable=False)
    data = Column(JSON, nullable=False)
    status = Column(String, nullable=True)

    def mark_as_processed(self):
        self.processed_at = datetime.now()
        self.status = self.STATUS_DONE


class LogRepository:
    """
    Repository - interacts with database, operating on ProcessedElementLog model
    """

    _orm: ORM

    def __init__(self, orm: ORM):
        self._orm = orm

    def persist(self, log: ProcessedElementLog):
        self._orm.session.add(log)
        self._orm.session.flush([log])

    def find_last_processed_element_date(self, entry_type: str) -> datetime:
        return self._orm.session.query(func.max(ProcessedElementLog.element_date))\
            .filter(ProcessedElementLog.element_type == entry_type)\
            .scalar()

    def find(self, entry_type: str, entry_id: str) -> ProcessedElementLog:
        return self._orm.session.query(ProcessedElementLog)\
            .filter(ProcessedElementLog.element_type == entry_type, ProcessedElementLog.element_id == entry_id)\
            .limit(1)\
            .one()

    def find_or_create(self, entry_type: str, entry_id: str, date: datetime, tz: str, form: str) -> ProcessedElementLog:
        try:
            return self.find(entry_type, entry_id)

        except ORMNoResultFound:
            log = ProcessedElementLog()
            log.element_id = entry_id
            log.element_type = entry_type
            log.element_date = date
            log.element_tz = tz
            log.data = form
            log.processed_at = datetime.now()

            return log

    def was_already_processed(self, entry_type: str, entry_id: str):
        try:
            element = self.find(entry_type, entry_id)
            return element.status == ProcessedElementLog.STATUS_DONE

        except ORMNoResultFound:
            return False
