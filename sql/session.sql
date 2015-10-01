CREATE TABLE ss.sessions
(
  ss_session_id character varying(40) NOT NULL,
  ss_session_started timestamp(0) without time zone DEFAULT now(),
  ss_session_expires timestamp(0) without time zone NOT NULL,
  ss_session_last_requested timestamp(0) without time zone DEFAULT now(),
  ss_session_pages_count integer NOT NULL DEFAULT 0,
  ss_session_user_ip text,
  ss_session_user_id text,
  ss_session_values text NOT NULL,
  CONSTRAINT sessions_pk PRIMARY KEY (ss_session_id)
);

CREATE INDEX ss_session_expires_idx
  ON ss.sessions
  USING btree
  (ss_session_expires);

CREATE INDEX ss_session_user_id_idx
  ON ss.sessions
  USING btree
  (ss_session_user_id COLLATE pg_catalog."default");