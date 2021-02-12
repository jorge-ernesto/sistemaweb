--
-- PostgreSQL database dump
--

-- Dumped from database version 12.3
-- Dumped by pg_dump version 12.1

-- Started on 2021-02-10 08:53:12

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

DROP DATABASE opensoft;
--
-- TOC entry 3609 (class 1262 OID 37521)
-- Name: opensoft; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE opensoft WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8';


ALTER DATABASE opensoft OWNER TO postgres;

\connect opensoft

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 302 (class 1255 OID 37522)
-- Name: first_agg(anyelement, anyelement); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.first_agg(anyelement, anyelement) RETURNS anyelement
    LANGUAGE sql IMMUTABLE STRICT
    AS $_$
        SELECT $1;
$_$;


ALTER FUNCTION public.first_agg(anyelement, anyelement) OWNER TO postgres;

--
-- TOC entry 303 (class 1255 OID 37523)
-- Name: last_agg(anyelement, anyelement); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.last_agg(anyelement, anyelement) RETURNS anyelement
    LANGUAGE sql IMMUTABLE STRICT
    AS $_$
        SELECT $2;
$_$;


ALTER FUNCTION public.last_agg(anyelement, anyelement) OWNER TO postgres;

--
-- TOC entry 984 (class 1255 OID 37524)
-- Name: first(anyelement); Type: AGGREGATE; Schema: public; Owner: postgres
--

CREATE AGGREGATE public.first(anyelement) (
    SFUNC = public.first_agg,
    STYPE = anyelement
);


ALTER AGGREGATE public.first(anyelement) OWNER TO postgres;

--
-- TOC entry 985 (class 1255 OID 37525)
-- Name: last(anyelement); Type: AGGREGATE; Schema: public; Owner: postgres
--

CREATE AGGREGATE public.last(anyelement) (
    SFUNC = public.last_agg,
    STYPE = anyelement
);


ALTER AGGREGATE public.last(anyelement) OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 202 (class 1259 OID 37526)
-- Name: ad_entity; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ad_entity (
    ad_entity_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    classname character varying(255) NOT NULL,
    currentversion numeric(12,0) NOT NULL,
    isreport numeric(1,0) NOT NULL
);


ALTER TABLE public.ad_entity OWNER TO postgres;

--
-- TOC entry 203 (class 1259 OID 37532)
-- Name: ad_role; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ad_role (
    ad_role_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255)
);


ALTER TABLE public.ad_role OWNER TO postgres;

--
-- TOC entry 204 (class 1259 OID 37535)
-- Name: ad_role_access; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ad_role_access (
    ad_role_access_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    ad_entity_id numeric(20,0) NOT NULL,
    ad_role_id numeric(20,0) NOT NULL,
    canread numeric(1,0) NOT NULL,
    canwrite numeric(1,0) NOT NULL,
    canexecute numeric(1,0) NOT NULL,
    canadmin numeric(1,0) NOT NULL
);


ALTER TABLE public.ad_role_access OWNER TO postgres;

--
-- TOC entry 205 (class 1259 OID 37538)
-- Name: ad_role_user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ad_role_user (
    ad_role_user_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    ad_role_id numeric(20,0) NOT NULL,
    ad_user_id numeric(20,0) NOT NULL
);


ALTER TABLE public.ad_role_user OWNER TO postgres;

--
-- TOC entry 206 (class 1259 OID 37541)
-- Name: ad_user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ad_user (
    ad_user_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    loginname character varying(64) NOT NULL,
    password character varying(255) NOT NULL
);


ALTER TABLE public.ad_user OWNER TO postgres;

--
-- TOC entry 207 (class 1259 OID 37544)
-- Name: seq_c_bpartner_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_c_bpartner_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_c_bpartner_id OWNER TO postgres;

--
-- TOC entry 208 (class 1259 OID 37546)
-- Name: c_bpartner; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_bpartner (
    c_bpartner_id numeric(20,0) DEFAULT nextval('public.seq_c_bpartner_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(100) NOT NULL,
    description character varying(255),
    taxid character varying(32),
    value character varying(32) NOT NULL,
    isworker numeric(1,0) NOT NULL
);


ALTER TABLE public.c_bpartner OWNER TO postgres;

--
-- TOC entry 209 (class 1259 OID 37550)
-- Name: c_cashdeposit; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_cashdeposit (
    c_cashdeposit_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL,
    h_worker_id numeric(20,0) NOT NULL,
    c_currency_id numeric(20,0) NOT NULL,
    c_periodcontrol_id numeric(20,0),
    amount numeric(12,4) NOT NULL
);


ALTER TABLE public.c_cashdeposit OWNER TO postgres;

--
-- TOC entry 210 (class 1259 OID 37553)
-- Name: c_client; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_client (
    c_client_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    taxid character varying(32) NOT NULL,
    postaladdress character varying(255),
    value character varying(8) NOT NULL,
    connection_string character varying
);


ALTER TABLE public.c_client OWNER TO postgres;

--
-- TOC entry 211 (class 1259 OID 37559)
-- Name: c_config; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_config (
    c_config_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0),
    name character varying(64) NOT NULL,
    value character varying(255) NOT NULL
);


ALTER TABLE public.c_config OWNER TO postgres;

--
-- TOC entry 212 (class 1259 OID 37562)
-- Name: c_currency; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_currency (
    c_currency_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    abbreviation character varying(8) NOT NULL
);


ALTER TABLE public.c_currency OWNER TO postgres;

--
-- TOC entry 213 (class 1259 OID 37565)
-- Name: sep_c_daycontrol_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sep_c_daycontrol_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sep_c_daycontrol_id OWNER TO postgres;

--
-- TOC entry 214 (class 1259 OID 37567)
-- Name: c_daycontrol; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_daycontrol (
    c_daycontrol_id numeric(20,0) DEFAULT nextval('public.sep_c_daycontrol_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    systemdate date NOT NULL,
    isclosed numeric(1,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL
);


ALTER TABLE public.c_daycontrol OWNER TO postgres;

--
-- TOC entry 215 (class 1259 OID 37571)
-- Name: c_doctype; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_doctype (
    c_doctype_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    value character varying(8) NOT NULL,
    nu_doctype_sunat character varying(8)
);


ALTER TABLE public.c_doctype OWNER TO postgres;

--
-- TOC entry 216 (class 1259 OID 37574)
-- Name: c_documentserial; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_documentserial (
    c_documentserial_id numeric(20,0) NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone DEFAULT now() NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL,
    c_doctype_id numeric(20,0) NOT NULL,
    documentserial character varying(16) NOT NULL,
    currentvalue numeric(20,0) NOT NULL,
    isautomatic numeric(1,0) NOT NULL
);


ALTER TABLE public.c_documentserial OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 37579)
-- Name: seq_c_invoicedetail_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_c_invoicedetail_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_c_invoicedetail_id OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 37581)
-- Name: c_invoicedetail; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_invoicedetail (
    c_invoicedetail_id numeric(20,0) DEFAULT nextval('public.seq_c_invoicedetail_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_invoiceheader_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    unitprice numeric(12,4) NOT NULL,
    linetotal numeric(12,4) NOT NULL,
    quantity numeric(12,4) NOT NULL
);


ALTER TABLE public.c_invoicedetail OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 37585)
-- Name: seq_c_invoiceheader_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_c_invoiceheader_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_c_invoiceheader_id OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 37587)
-- Name: c_invoiceheader; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_invoiceheader (
    c_invoiceheader_id numeric(20,0) DEFAULT nextval('public.seq_c_invoiceheader_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL,
    c_bpartner_id numeric(20,0) NOT NULL,
    c_currency_id numeric(20,0) NOT NULL,
    issale numeric(1,0) NOT NULL,
    c_tendertype_id numeric(20,0) NOT NULL,
    status numeric(1,0) NOT NULL,
    documentno character varying(64) NOT NULL,
    c_doctype_id numeric(20,0) NOT NULL,
    documentserial character varying(64),
    ap character varying(15),
    plate character varying(20),
    turn numeric(1,0),
    reference_documentno character varying(40)
);


ALTER TABLE public.c_invoiceheader OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 37591)
-- Name: seq_c_invoicetax_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_c_invoicetax_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_c_invoicetax_id OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 37593)
-- Name: c_invoicetax; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_invoicetax (
    c_invoicetax_id numeric(20,0) DEFAULT nextval('public.seq_c_invoicetax_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_invoiceheader_id numeric(20,0) NOT NULL,
    c_tax_id numeric(20,0) NOT NULL,
    baseamount numeric(12,4) NOT NULL,
    taxamount numeric(12,4) NOT NULL
);


ALTER TABLE public.c_invoicetax OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 37597)
-- Name: c_org; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_org (
    c_org_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255) NOT NULL,
    value character varying(8) NOT NULL,
    postaladdress character varying(255),
    initials character varying(10)
);


ALTER TABLE public.c_org OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 37603)
-- Name: c_org_access; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_org_access (
    c_org_access_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL,
    ad_role_id numeric(20,0) NOT NULL
);


ALTER TABLE public.c_org_access OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 37606)
-- Name: sep_c_periodcontrol_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sep_c_periodcontrol_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sep_c_periodcontrol_id OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 37608)
-- Name: c_periodcontrol; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_periodcontrol (
    c_periodcontrol_id numeric(20,0) DEFAULT nextval('public.sep_c_periodcontrol_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_daycontrol_id numeric(20,0) NOT NULL,
    isclosed numeric(1,0) NOT NULL,
    c_org_id numeric(20,0)
);


ALTER TABLE public.c_periodcontrol OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 37612)
-- Name: c_pos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_pos (
    c_pos_id numeric(20,0) NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone DEFAULT now() NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL,
    c_documentserial_id numeric(20,0) NOT NULL,
    i_warehouse_id numeric(20,0) NOT NULL,
    c_postype_id numeric(20,0) NOT NULL,
    terminaldata character varying(255) NOT NULL
);


ALTER TABLE public.c_pos OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 37617)
-- Name: c_postype; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_postype (
    c_postype_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    classname character varying(255) NOT NULL
);


ALTER TABLE public.c_postype OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 37623)
-- Name: sep_c_pricelistdetail_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sep_c_pricelistdetail_id
    START WITH 1293
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sep_c_pricelistdetail_id OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 37625)
-- Name: c_pricelistdetail; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_pricelistdetail (
    c_pricelistdetail_id numeric(20,0) DEFAULT nextval('public.sep_c_pricelistdetail_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_pricelistheader_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    minprice numeric(12,4) NOT NULL,
    defprice numeric(12,4) NOT NULL,
    maxprice numeric(12,4) NOT NULL
);


ALTER TABLE public.c_pricelistdetail OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 37629)
-- Name: sep_c_pricelistheader_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sep_c_pricelistheader_id
    START WITH 1293
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sep_c_pricelistheader_id OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 37631)
-- Name: c_pricelistheader; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_pricelistheader (
    c_pricelistheader_id numeric(20,0) DEFAULT nextval('public.sep_c_pricelistheader_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    c_client_id numeric(20,0) NOT NULL,
    c_currency_id numeric(20,0) NOT NULL
);


ALTER TABLE public.c_pricelistheader OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 37635)
-- Name: seq_c_product_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_c_product_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_c_product_id OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 37637)
-- Name: c_product; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_product (
    c_product_id numeric(20,0) DEFAULT nextval('public.seq_c_product_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_productfamily_id numeric(20,0) NOT NULL,
    c_productuom_id numeric(20,0) NOT NULL,
    c_productgroup_id numeric(20,0),
    c_taxgroup_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    iscomposite numeric(1,0) NOT NULL,
    issellable numeric(1,0) NOT NULL,
    isinventory numeric(1,0) NOT NULL,
    value character varying(16) NOT NULL
);


ALTER TABLE public.c_product OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 37641)
-- Name: c_productfamily; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_productfamily (
    c_productfamily_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_producttype_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255)
);


ALTER TABLE public.c_productfamily OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 37644)
-- Name: c_productgroup; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_productgroup (
    c_productgroup_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255)
);


ALTER TABLE public.c_productgroup OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 37647)
-- Name: c_productlink; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_productlink (
    c_productlink_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    linked_c_product_id numeric(20,0) NOT NULL,
    quantity numeric(12,4) NOT NULL
);


ALTER TABLE public.c_productlink OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 37650)
-- Name: c_producttype; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_producttype (
    c_producttype_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255)
);


ALTER TABLE public.c_producttype OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 37653)
-- Name: c_productuom; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_productuom (
    c_productuom_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    abbreviation character varying(8) NOT NULL
);


ALTER TABLE public.c_productuom OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 37656)
-- Name: c_promo_org; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_promo_org (
    c_promo_org_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_promoheader_id numeric(20,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL
);


ALTER TABLE public.c_promo_org OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 37659)
-- Name: c_promodetail; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_promodetail (
    c_promodetail_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_productgroup_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    validfrom date NOT NULL,
    validto date NOT NULL,
    istargetprice numeric(1,0) NOT NULL
);


ALTER TABLE public.c_promodetail OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 37662)
-- Name: c_promoheader; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_promoheader (
    c_promoheader_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255)
);


ALTER TABLE public.c_promoheader OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 37665)
-- Name: seq_c_sale_shift_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_c_sale_shift_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_c_sale_shift_id OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 37667)
-- Name: c_sale_shift; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_sale_shift (
    c_sale_shift_id numeric(20,0) DEFAULT nextval('public.seq_c_sale_shift_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_pos_id numeric(20,0) NOT NULL,
    c_periodcontrol_id numeric(20,0) NOT NULL,
    number_zz numeric(20,0) NOT NULL,
    documentno_initial character varying(64) NOT NULL,
    documentno_final character varying(64) NOT NULL,
    ticket_transactions numeric(12,0),
    ticket_total numeric(14,4),
    ticket_tax numeric(14,4),
    invoice_transactions numeric(12,0),
    invoice_total numeric(14,4),
    invoice_tax numeric(14,4),
    total_transactions numeric(12,0),
    total_total numeric(14,4),
    total_tax numeric(14,4),
    cash_number integer
);


ALTER TABLE public.c_sale_shift OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 37671)
-- Name: c_tax; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_tax (
    c_tax_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(64),
    percentage numeric(12,4) NOT NULL
);


ALTER TABLE public.c_tax OWNER TO postgres;

--
-- TOC entry 246 (class 1259 OID 37674)
-- Name: c_taxgroup; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_taxgroup (
    c_taxgroup_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255)
);


ALTER TABLE public.c_taxgroup OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 37677)
-- Name: c_taxgroup_tax; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_taxgroup_tax (
    c_taxgroup_tax_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_taxgroup_id numeric(20,0) NOT NULL,
    c_tax_id numeric(20,0) NOT NULL,
    validfrom date NOT NULL,
    validto date NOT NULL
);


ALTER TABLE public.c_taxgroup_tax OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 37680)
-- Name: c_tendertype; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_tendertype (
    c_tendertype_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    iscredit numeric(1,0) NOT NULL,
    duedays numeric(12,0) NOT NULL
);


ALTER TABLE public.c_tendertype OWNER TO postgres;

--
-- TOC entry 249 (class 1259 OID 37683)
-- Name: f_driver; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_driver (
    f_driver_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    classname character varying(255) NOT NULL
);


ALTER TABLE public.f_driver OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 37689)
-- Name: f_fleet; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_fleet (
    f_fleet_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    f_fleetprovider_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    providerdata character varying(255) NOT NULL,
    ismandatory numeric(1,0) NOT NULL
);


ALTER TABLE public.f_fleet OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 37695)
-- Name: f_fleetdifference; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_fleetdifference (
    f_fleetdifference_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL,
    c_invoiceheader_id numeric(20,0),
    i_movementheader_id numeric(20,0),
    f_fleetvehicle_id numeric(20,0) NOT NULL,
    differencetype numeric(1,0) NOT NULL,
    differencevalue numeric(12,4) NOT NULL
);


ALTER TABLE public.f_fleetdifference OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 37698)
-- Name: f_fleetgroup; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_fleetgroup (
    f_fleetgroup_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    f_fleet_id numeric(20,0) NOT NULL,
    c_bpartner_id numeric(20,0) NOT NULL,
    differencetype numeric(1,0) NOT NULL,
    differencevalue numeric(12,4) NOT NULL
);


ALTER TABLE public.f_fleetgroup OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 37701)
-- Name: f_fleetlockoutreason; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_fleetlockoutreason (
    f_fleetlockoutreason_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    f_fleetprovider_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    value character varying(8) NOT NULL
);


ALTER TABLE public.f_fleetlockoutreason OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 37704)
-- Name: f_fleetprovider; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_fleetprovider (
    f_fleetprovider_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    iscentralized numeric(1,0) NOT NULL
);


ALTER TABLE public.f_fleetprovider OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 37707)
-- Name: f_fleetvehicle; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_fleetvehicle (
    f_fleetvehicle_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    f_fleetgroup_id numeric(20,0) NOT NULL,
    f_fleetlockoutreason_id numeric(20,0),
    vehicleregister character varying(16) NOT NULL,
    vehicleid character varying(32) NOT NULL,
    differencetype numeric(1,0) NOT NULL,
    differencevalue numeric(12,4) NOT NULL
);


ALTER TABLE public.f_fleetvehicle OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 37710)
-- Name: seq_f_grade_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_f_grade_id
    START WITH 33696
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_f_grade_id OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 37712)
-- Name: f_grade; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_grade (
    f_grade_id numeric(20,0) DEFAULT nextval('public.seq_f_grade_id'::regclass) NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone DEFAULT now() NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    f_pump_id numeric(20,0) NOT NULL,
    value character varying(8) NOT NULL,
    interfacedata character varying(255) NOT NULL,
    c_product_id numeric(20,0) NOT NULL
);


ALTER TABLE public.f_grade OWNER TO postgres;

--
-- TOC entry 258 (class 1259 OID 37718)
-- Name: seq_f_pump_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_f_pump_id
    START WITH 33696
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_f_pump_id OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 37720)
-- Name: f_pump; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_pump (
    f_pump_id numeric(20,0) DEFAULT nextval('public.seq_f_pump_id'::regclass) NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone DEFAULT now() NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    f_pumpnetwork_id numeric(20,0) NOT NULL,
    value character varying(8) NOT NULL,
    driverdata character varying(255) NOT NULL,
    f_fleet_id numeric(20,0) NOT NULL
);


ALTER TABLE public.f_pump OWNER TO postgres;

--
-- TOC entry 260 (class 1259 OID 37726)
-- Name: f_pump_pos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_pump_pos (
    f_pump_pos_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_pos_id numeric(20,0) NOT NULL,
    f_pump_id numeric(20,0) NOT NULL
);


ALTER TABLE public.f_pump_pos OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 37729)
-- Name: f_pump_worker; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_pump_worker (
    f_pump_worker_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_periodcontrol_id numeric(20,0) NOT NULL,
    h_worker_id numeric(20,0) NOT NULL,
    f_pump_id numeric(20,0) NOT NULL
);


ALTER TABLE public.f_pump_worker OWNER TO postgres;

--
-- TOC entry 262 (class 1259 OID 37732)
-- Name: f_pumpnetwork; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_pumpnetwork (
    f_pumpnetwork_id numeric(20,0) NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone DEFAULT now() NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL,
    f_driver_id numeric(20,0) NOT NULL,
    driverdata character varying(255) NOT NULL,
    name character varying(64) NOT NULL
);


ALTER TABLE public.f_pumpnetwork OWNER TO postgres;

--
-- TOC entry 263 (class 1259 OID 37737)
-- Name: f_sale; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_sale (
    f_sale_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    f_grade_id numeric(20,0) NOT NULL,
    volume numeric(12,4) NOT NULL,
    amount numeric(12,4) NOT NULL,
    price numeric(12,4) NOT NULL,
    totalizervolume numeric(12,4) NOT NULL,
    totalizeramount numeric(12,4) NOT NULL
);


ALTER TABLE public.f_sale OWNER TO postgres;

--
-- TOC entry 264 (class 1259 OID 37740)
-- Name: seq_f_totalizer_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_f_totalizer_id
    START WITH 22314
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_f_totalizer_id OWNER TO postgres;

--
-- TOC entry 265 (class 1259 OID 37742)
-- Name: f_totalizer; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.f_totalizer (
    f_totalizer_id numeric(20,0) DEFAULT nextval('public.seq_f_totalizer_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    f_grade_id numeric(20,0) NOT NULL,
    volume numeric(12,4) NOT NULL,
    amount numeric(12,4) NOT NULL,
    c_periodcontrol_id numeric(20,0) NOT NULL
);


ALTER TABLE public.f_totalizer OWNER TO postgres;

--
-- TOC entry 266 (class 1259 OID 37746)
-- Name: h_worker; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.h_worker (
    h_worker_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_bpartner_id numeric(20,0) NOT NULL
);


ALTER TABLE public.h_worker OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 37749)
-- Name: seq_i_inventorydetail_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_i_inventorydetail_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_i_inventorydetail_id OWNER TO postgres;

--
-- TOC entry 268 (class 1259 OID 37751)
-- Name: i_inventorydetail; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_inventorydetail (
    i_inventorydetail_id numeric(20,0) DEFAULT nextval('public.seq_i_inventorydetail_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    i_inventoryheader_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    quantity numeric(12,4) NOT NULL
);


ALTER TABLE public.i_inventorydetail OWNER TO postgres;

--
-- TOC entry 269 (class 1259 OID 37755)
-- Name: seq_i_inventoryheader_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_i_inventoryheader_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_i_inventoryheader_id OWNER TO postgres;

--
-- TOC entry 270 (class 1259 OID 37757)
-- Name: i_inventoryheader; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_inventoryheader (
    i_inventoryheader_id numeric(20,0) DEFAULT nextval('public.seq_i_inventoryheader_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    i_warehouselocation_id numeric(20,0) NOT NULL
);


ALTER TABLE public.i_inventoryheader OWNER TO postgres;

--
-- TOC entry 271 (class 1259 OID 37761)
-- Name: seq_i_movementdetail_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_i_movementdetail_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_i_movementdetail_id OWNER TO postgres;

--
-- TOC entry 272 (class 1259 OID 37763)
-- Name: i_movementdetail; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_movementdetail (
    i_movementdetail_id numeric(20,0) DEFAULT nextval('public.seq_i_movementdetail_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    i_movementheader_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    quantity numeric(12,4) NOT NULL,
    unitprice numeric(12,4) NOT NULL,
    linetotal numeric(12,4) NOT NULL
);


ALTER TABLE public.i_movementdetail OWNER TO postgres;

--
-- TOC entry 273 (class 1259 OID 37767)
-- Name: seq_i_movementheader_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_i_movementheader_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_i_movementheader_id OWNER TO postgres;

--
-- TOC entry 274 (class 1259 OID 37769)
-- Name: i_movementheader; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_movementheader (
    i_movementheader_id numeric(20,0) DEFAULT nextval('public.seq_i_movementheader_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    source_i_warehouse_id numeric(20,0),
    destination_i_warehouse_id numeric(20,0),
    c_bpartner_id numeric(20,0) NOT NULL,
    c_doctype_id numeric(20,0),
    documentserial character varying(16) NOT NULL,
    documentno character varying(20) NOT NULL,
    c_invoiceheader_id numeric(20,0),
    status numeric(1,0) NOT NULL,
    c_org_id numeric(20,0),
    f_fleetvehicle_id numeric(20,0),
    f_pump_id numeric(20,0),
    i_movementtype_id numeric(20,0),
    num_mov character varying(15)
);


ALTER TABLE public.i_movementheader OWNER TO postgres;

--
-- TOC entry 275 (class 1259 OID 37773)
-- Name: i_movementtype; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_movementtype (
    i_movementtype_id numeric(20,0) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    value character varying(16) NOT NULL,
    optype numeric(1,0),
    iscosted numeric(1,0)
);


ALTER TABLE public.i_movementtype OWNER TO postgres;

--
-- TOC entry 276 (class 1259 OID 37776)
-- Name: i_product_warehouselocation; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_product_warehouselocation (
    i_product_warehouselocation_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    i_warehouselocation_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL
);


ALTER TABLE public.i_product_warehouselocation OWNER TO postgres;

--
-- TOC entry 277 (class 1259 OID 37779)
-- Name: i_stock; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_stock (
    i_stock_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    i_warehouse_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    quantity numeric(20,4) NOT NULL,
    amount numeric(12,4) NOT NULL
);


ALTER TABLE public.i_stock OWNER TO postgres;

--
-- TOC entry 278 (class 1259 OID 37782)
-- Name: i_stockcontrol; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_stockcontrol (
    i_stockcontrol_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    i_warehouse_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    minstock numeric(12,4) NOT NULL,
    maxstock numeric(12,4) NOT NULL
);


ALTER TABLE public.i_stockcontrol OWNER TO postgres;

--
-- TOC entry 279 (class 1259 OID 37785)
-- Name: i_warehouse; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_warehouse (
    i_warehouse_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255) NOT NULL,
    isinternal numeric(1,0) NOT NULL,
    isprovider numeric(1,0) NOT NULL
);


ALTER TABLE public.i_warehouse OWNER TO postgres;

--
-- TOC entry 280 (class 1259 OID 37788)
-- Name: i_warehouselocation; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.i_warehouselocation (
    i_warehouselocation_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    i_warehouse_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255)
);


ALTER TABLE public.i_warehouselocation OWNER TO postgres;

--
-- TOC entry 281 (class 1259 OID 37791)
-- Name: ip; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ip (
    id numeric(20,0) NOT NULL,
    nombre character varying(50),
    ip character varying(20)
);


ALTER TABLE public.ip OWNER TO postgres;

--
-- TOC entry 282 (class 1259 OID 37794)
-- Name: l_account; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_account (
    l_account_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    l_accounttype_id numeric(20,0) NOT NULL,
    c_bpartner_id numeric(20,0) NOT NULL,
    accountno numeric(12,0) NOT NULL
);


ALTER TABLE public.l_account OWNER TO postgres;

--
-- TOC entry 283 (class 1259 OID 37797)
-- Name: l_accounttype; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_accounttype (
    l_accounttype_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    l_group_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255)
);


ALTER TABLE public.l_accounttype OWNER TO postgres;

--
-- TOC entry 284 (class 1259 OID 37800)
-- Name: l_campaign; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_campaign (
    l_campaign_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    l_group_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    validfrom date NOT NULL,
    validto date NOT NULL,
    dailyretention numeric(12,0) NOT NULL,
    espirationdays numeric(12,0) NOT NULL,
    slogan character varying(512) NOT NULL
);


ALTER TABLE public.l_campaign OWNER TO postgres;

--
-- TOC entry 285 (class 1259 OID 37806)
-- Name: l_campaign_accounttype; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_campaign_accounttype (
    l_campaign_accounttype_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    l_campaign_id numeric(20,0) NOT NULL,
    l_accounttype_id numeric(20,0) NOT NULL
);


ALTER TABLE public.l_campaign_accounttype OWNER TO postgres;

--
-- TOC entry 286 (class 1259 OID 37809)
-- Name: l_card; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_card (
    l_card_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    l_account_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL
);


ALTER TABLE public.l_card OWNER TO postgres;

--
-- TOC entry 287 (class 1259 OID 37812)
-- Name: l_group; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_group (
    l_group_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255)
);


ALTER TABLE public.l_group OWNER TO postgres;

--
-- TOC entry 288 (class 1259 OID 37815)
-- Name: l_movement; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_movement (
    l_movement_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    l_card_id numeric(20,0) NOT NULL,
    l_org_id numeric(20,0) NOT NULL,
    movementtype numeric(12,0) NOT NULL,
    movementvalue numeric(12,4) NOT NULL
);


ALTER TABLE public.l_movement OWNER TO postgres;

--
-- TOC entry 289 (class 1259 OID 37818)
-- Name: l_org; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_org (
    l_org_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    l_group_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    value character varying(8) NOT NULL
);


ALTER TABLE public.l_org OWNER TO postgres;

--
-- TOC entry 290 (class 1259 OID 37821)
-- Name: l_prize; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_prize (
    l_prize_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    l_campaign_id numeric(20,0) NOT NULL,
    c_currency_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    description character varying(255),
    pointcost numeric(12,4) NOT NULL,
    cashcost numeric(12,4) NOT NULL
);


ALTER TABLE public.l_prize OWNER TO postgres;

--
-- TOC entry 291 (class 1259 OID 37824)
-- Name: l_productpoints; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.l_productpoints (
    l_productpoints_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    createdby numeric(20,0) NOT NULL,
    updated timestamp without time zone NOT NULL,
    updatedby numeric(20,0) NOT NULL,
    isactive numeric(1,0) NOT NULL,
    l_campaign_id numeric(20,0) NOT NULL,
    c_product_id numeric(20,0) NOT NULL,
    assignmenttype numeric(1,0) NOT NULL,
    assignmentvalue numeric(12,4) NOT NULL
);


ALTER TABLE public.l_productpoints OWNER TO postgres;

--
-- TOC entry 292 (class 1259 OID 37827)
-- Name: mig_cowmap; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mig_cowmap (
    ch_almacen character(3) NOT NULL,
    c_client_id numeric(20,0) NOT NULL,
    c_org_id numeric(20,0) NOT NULL,
    i_warehouse_id numeric(20,0) NOT NULL,
    id_remote numeric(20,0)
);


ALTER TABLE public.mig_cowmap OWNER TO postgres;

--
-- TOC entry 293 (class 1259 OID 37830)
-- Name: seq_mig_export_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_mig_export_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_mig_export_id OWNER TO postgres;

--
-- TOC entry 294 (class 1259 OID 37832)
-- Name: mig_export; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mig_export (
    mig_export_id numeric(20,0) DEFAULT nextval('public.seq_mig_export_id'::regclass) NOT NULL,
    created timestamp without time zone NOT NULL,
    systemdate date NOT NULL,
    creado character(28),
    mig_remote_id numeric(20,0)
);


ALTER TABLE public.mig_export OWNER TO postgres;

--
-- TOC entry 295 (class 1259 OID 37836)
-- Name: seq_mig_process_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_mig_process_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_mig_process_id OWNER TO postgres;

--
-- TOC entry 296 (class 1259 OID 37838)
-- Name: mig_process; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mig_process (
    mig_process_id numeric(20,0) DEFAULT nextval('public.seq_mig_process_id'::regclass) NOT NULL,
    mig_remote_id numeric(20,0) NOT NULL,
    created timestamp without time zone NOT NULL,
    systemdate date NOT NULL,
    status numeric(1,0)
);


ALTER TABLE public.mig_process OWNER TO postgres;

--
-- TOC entry 297 (class 1259 OID 37842)
-- Name: mig_remote; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mig_remote (
    mig_remote_id numeric(20,0) NOT NULL,
    name character varying(64) NOT NULL,
    ip character varying(16) NOT NULL,
    view integer
);


ALTER TABLE public.mig_remote OWNER TO postgres;

--
-- TOC entry 298 (class 1259 OID 37845)
-- Name: numero_items; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.numero_items
    START WITH 0
    INCREMENT BY 1
    MINVALUE 0
    MAXVALUE 20
    CACHE 1
    CYCLE;


ALTER TABLE public.numero_items OWNER TO postgres;

--
-- TOC entry 299 (class 1259 OID 37847)
-- Name: seq_c_daycontrol_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_c_daycontrol_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_c_daycontrol_id OWNER TO postgres;

--
-- TOC entry 300 (class 1259 OID 37849)
-- Name: seq_c_periodcontrol_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_c_periodcontrol_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_c_periodcontrol_id OWNER TO postgres;

--
-- TOC entry 301 (class 1259 OID 37851)
-- Name: seq_i_movementtype_id; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_i_movementtype_id
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_i_movementtype_id OWNER TO postgres;

--
-- TOC entry 3205 (class 2606 OID 37858)
-- Name: ad_entity pk_ad_entity; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_entity
    ADD CONSTRAINT pk_ad_entity PRIMARY KEY (ad_entity_id);


--
-- TOC entry 3207 (class 2606 OID 37860)
-- Name: ad_role pk_ad_role; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_role
    ADD CONSTRAINT pk_ad_role PRIMARY KEY (ad_role_id);


--
-- TOC entry 3209 (class 2606 OID 37862)
-- Name: ad_role_access pk_ad_role_access; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_role_access
    ADD CONSTRAINT pk_ad_role_access PRIMARY KEY (ad_role_access_id);


--
-- TOC entry 3211 (class 2606 OID 37864)
-- Name: ad_role_user pk_ad_role_user; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_role_user
    ADD CONSTRAINT pk_ad_role_user PRIMARY KEY (ad_role_user_id);


--
-- TOC entry 3213 (class 2606 OID 37866)
-- Name: ad_user pk_ad_user; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_user
    ADD CONSTRAINT pk_ad_user PRIMARY KEY (ad_user_id);


--
-- TOC entry 3216 (class 2606 OID 37868)
-- Name: c_bpartner pk_c_bpartner; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_bpartner
    ADD CONSTRAINT pk_c_bpartner PRIMARY KEY (c_bpartner_id);


--
-- TOC entry 3218 (class 2606 OID 37870)
-- Name: c_cashdeposit pk_c_cashdeposit; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_cashdeposit
    ADD CONSTRAINT pk_c_cashdeposit PRIMARY KEY (c_cashdeposit_id);


--
-- TOC entry 3220 (class 2606 OID 37872)
-- Name: c_client pk_c_client; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_client
    ADD CONSTRAINT pk_c_client PRIMARY KEY (c_client_id);


--
-- TOC entry 3222 (class 2606 OID 37874)
-- Name: c_config pk_c_config; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_config
    ADD CONSTRAINT pk_c_config PRIMARY KEY (c_config_id);


--
-- TOC entry 3224 (class 2606 OID 37876)
-- Name: c_currency pk_c_currency; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_currency
    ADD CONSTRAINT pk_c_currency PRIMARY KEY (c_currency_id);


--
-- TOC entry 3226 (class 2606 OID 37878)
-- Name: c_daycontrol pk_c_daycontrol; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_daycontrol
    ADD CONSTRAINT pk_c_daycontrol PRIMARY KEY (c_daycontrol_id);


--
-- TOC entry 3228 (class 2606 OID 37880)
-- Name: c_doctype pk_c_doctype; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_doctype
    ADD CONSTRAINT pk_c_doctype PRIMARY KEY (c_doctype_id);


--
-- TOC entry 3230 (class 2606 OID 37882)
-- Name: c_documentserial pk_c_documentserial; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_documentserial
    ADD CONSTRAINT pk_c_documentserial PRIMARY KEY (c_documentserial_id);


--
-- TOC entry 3232 (class 2606 OID 37884)
-- Name: c_invoicedetail pk_c_invoicedetail; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoicedetail
    ADD CONSTRAINT pk_c_invoicedetail PRIMARY KEY (c_invoicedetail_id);


--
-- TOC entry 3235 (class 2606 OID 37890)
-- Name: c_invoiceheader pk_c_invoiceheader; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoiceheader
    ADD CONSTRAINT pk_c_invoiceheader PRIMARY KEY (c_invoiceheader_id);


--
-- TOC entry 3237 (class 2606 OID 37892)
-- Name: c_invoicetax pk_c_invoicetax; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoicetax
    ADD CONSTRAINT pk_c_invoicetax PRIMARY KEY (c_invoicetax_id);


--
-- TOC entry 3239 (class 2606 OID 37894)
-- Name: c_org pk_c_org; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_org
    ADD CONSTRAINT pk_c_org PRIMARY KEY (c_org_id);


--
-- TOC entry 3241 (class 2606 OID 37896)
-- Name: c_org_access pk_c_org_access; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_org_access
    ADD CONSTRAINT pk_c_org_access PRIMARY KEY (c_org_access_id);


--
-- TOC entry 3243 (class 2606 OID 37898)
-- Name: c_periodcontrol pk_c_periodcontrol; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_periodcontrol
    ADD CONSTRAINT pk_c_periodcontrol PRIMARY KEY (c_periodcontrol_id);


--
-- TOC entry 3245 (class 2606 OID 37900)
-- Name: c_pos pk_c_pos; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pos
    ADD CONSTRAINT pk_c_pos PRIMARY KEY (c_pos_id);


--
-- TOC entry 3247 (class 2606 OID 37902)
-- Name: c_postype pk_c_postype; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_postype
    ADD CONSTRAINT pk_c_postype PRIMARY KEY (c_postype_id);


--
-- TOC entry 3249 (class 2606 OID 37904)
-- Name: c_pricelistdetail pk_c_pricelistdetail; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pricelistdetail
    ADD CONSTRAINT pk_c_pricelistdetail PRIMARY KEY (c_pricelistdetail_id);


--
-- TOC entry 3251 (class 2606 OID 37906)
-- Name: c_pricelistheader pk_c_pricelistheader; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pricelistheader
    ADD CONSTRAINT pk_c_pricelistheader PRIMARY KEY (c_pricelistheader_id);


--
-- TOC entry 3254 (class 2606 OID 37908)
-- Name: c_product pk_c_product; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_product
    ADD CONSTRAINT pk_c_product PRIMARY KEY (c_product_id);


--
-- TOC entry 3256 (class 2606 OID 37910)
-- Name: c_productfamily pk_c_productfamily; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_productfamily
    ADD CONSTRAINT pk_c_productfamily PRIMARY KEY (c_productfamily_id);


--
-- TOC entry 3258 (class 2606 OID 37912)
-- Name: c_productgroup pk_c_productgroup; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_productgroup
    ADD CONSTRAINT pk_c_productgroup PRIMARY KEY (c_productgroup_id);


--
-- TOC entry 3260 (class 2606 OID 37914)
-- Name: c_productlink pk_c_productlink; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_productlink
    ADD CONSTRAINT pk_c_productlink PRIMARY KEY (c_productlink_id);


--
-- TOC entry 3262 (class 2606 OID 37916)
-- Name: c_producttype pk_c_producttype; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_producttype
    ADD CONSTRAINT pk_c_producttype PRIMARY KEY (c_producttype_id);


--
-- TOC entry 3264 (class 2606 OID 37918)
-- Name: c_productuom pk_c_productuom; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_productuom
    ADD CONSTRAINT pk_c_productuom PRIMARY KEY (c_productuom_id);


--
-- TOC entry 3266 (class 2606 OID 37920)
-- Name: c_promo_org pk_c_promo_org; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_promo_org
    ADD CONSTRAINT pk_c_promo_org PRIMARY KEY (c_promo_org_id);


--
-- TOC entry 3268 (class 2606 OID 37922)
-- Name: c_promodetail pk_c_promodetail; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_promodetail
    ADD CONSTRAINT pk_c_promodetail PRIMARY KEY (c_promodetail_id);


--
-- TOC entry 3270 (class 2606 OID 37924)
-- Name: c_promoheader pk_c_promoheader; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_promoheader
    ADD CONSTRAINT pk_c_promoheader PRIMARY KEY (c_promoheader_id);


--
-- TOC entry 3272 (class 2606 OID 37926)
-- Name: c_sale_shift pk_c_sale_shift; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_sale_shift
    ADD CONSTRAINT pk_c_sale_shift PRIMARY KEY (c_sale_shift_id);


--
-- TOC entry 3274 (class 2606 OID 37928)
-- Name: c_tax pk_c_tax; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_tax
    ADD CONSTRAINT pk_c_tax PRIMARY KEY (c_tax_id);


--
-- TOC entry 3276 (class 2606 OID 37930)
-- Name: c_taxgroup pk_c_taxgroup; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_taxgroup
    ADD CONSTRAINT pk_c_taxgroup PRIMARY KEY (c_taxgroup_id);


--
-- TOC entry 3278 (class 2606 OID 37932)
-- Name: c_taxgroup_tax pk_c_taxgroup_tax; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_taxgroup_tax
    ADD CONSTRAINT pk_c_taxgroup_tax PRIMARY KEY (c_taxgroup_tax_id);


--
-- TOC entry 3280 (class 2606 OID 37934)
-- Name: c_tendertype pk_c_tendertype; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_tendertype
    ADD CONSTRAINT pk_c_tendertype PRIMARY KEY (c_tendertype_id);


--
-- TOC entry 3282 (class 2606 OID 37936)
-- Name: f_driver pk_f_driver; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_driver
    ADD CONSTRAINT pk_f_driver PRIMARY KEY (f_driver_id);


--
-- TOC entry 3284 (class 2606 OID 37938)
-- Name: f_fleet pk_f_fleet; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleet
    ADD CONSTRAINT pk_f_fleet PRIMARY KEY (f_fleet_id);


--
-- TOC entry 3286 (class 2606 OID 37940)
-- Name: f_fleetdifference pk_f_fleetdifference; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetdifference
    ADD CONSTRAINT pk_f_fleetdifference PRIMARY KEY (f_fleetdifference_id);


--
-- TOC entry 3288 (class 2606 OID 37942)
-- Name: f_fleetgroup pk_f_fleetgroup; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetgroup
    ADD CONSTRAINT pk_f_fleetgroup PRIMARY KEY (f_fleetgroup_id);


--
-- TOC entry 3290 (class 2606 OID 37944)
-- Name: f_fleetlockoutreason pk_f_fleetlockoutreason; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetlockoutreason
    ADD CONSTRAINT pk_f_fleetlockoutreason PRIMARY KEY (f_fleetlockoutreason_id);


--
-- TOC entry 3292 (class 2606 OID 37946)
-- Name: f_fleetprovider pk_f_fleetprovider; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetprovider
    ADD CONSTRAINT pk_f_fleetprovider PRIMARY KEY (f_fleetprovider_id);


--
-- TOC entry 3294 (class 2606 OID 37948)
-- Name: f_fleetvehicle pk_f_fleetvehicle; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetvehicle
    ADD CONSTRAINT pk_f_fleetvehicle PRIMARY KEY (f_fleetvehicle_id);


--
-- TOC entry 3296 (class 2606 OID 37950)
-- Name: f_grade pk_f_grade; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_grade
    ADD CONSTRAINT pk_f_grade PRIMARY KEY (f_grade_id);


--
-- TOC entry 3298 (class 2606 OID 37952)
-- Name: f_pump pk_f_pump; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump
    ADD CONSTRAINT pk_f_pump PRIMARY KEY (f_pump_id);


--
-- TOC entry 3300 (class 2606 OID 37954)
-- Name: f_pump_pos pk_f_pump_pod; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump_pos
    ADD CONSTRAINT pk_f_pump_pod PRIMARY KEY (f_pump_pos_id);


--
-- TOC entry 3302 (class 2606 OID 37956)
-- Name: f_pump_worker pk_f_pump_worker; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump_worker
    ADD CONSTRAINT pk_f_pump_worker PRIMARY KEY (f_pump_worker_id);


--
-- TOC entry 3304 (class 2606 OID 37958)
-- Name: f_pumpnetwork pk_f_pumpnetwork; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pumpnetwork
    ADD CONSTRAINT pk_f_pumpnetwork PRIMARY KEY (f_pumpnetwork_id);


--
-- TOC entry 3306 (class 2606 OID 37960)
-- Name: f_sale pk_f_sale; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_sale
    ADD CONSTRAINT pk_f_sale PRIMARY KEY (f_sale_id);


--
-- TOC entry 3308 (class 2606 OID 37962)
-- Name: f_totalizer pk_f_totalizer; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_totalizer
    ADD CONSTRAINT pk_f_totalizer PRIMARY KEY (f_totalizer_id);


--
-- TOC entry 3310 (class 2606 OID 37964)
-- Name: h_worker pk_h_worker; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.h_worker
    ADD CONSTRAINT pk_h_worker PRIMARY KEY (h_worker_id);


--
-- TOC entry 3312 (class 2606 OID 37966)
-- Name: i_inventorydetail pk_i_inventorydetail; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_inventorydetail
    ADD CONSTRAINT pk_i_inventorydetail PRIMARY KEY (i_inventorydetail_id);


--
-- TOC entry 3314 (class 2606 OID 37968)
-- Name: i_inventoryheader pk_i_inventoryheader; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_inventoryheader
    ADD CONSTRAINT pk_i_inventoryheader PRIMARY KEY (i_inventoryheader_id);


--
-- TOC entry 3316 (class 2606 OID 37970)
-- Name: i_movementdetail pk_i_movementdetail; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementdetail
    ADD CONSTRAINT pk_i_movementdetail PRIMARY KEY (i_movementdetail_id);


--
-- TOC entry 3319 (class 2606 OID 37972)
-- Name: i_movementheader pk_i_movementheader; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementheader
    ADD CONSTRAINT pk_i_movementheader PRIMARY KEY (i_movementheader_id);


--
-- TOC entry 3321 (class 2606 OID 37974)
-- Name: i_movementtype pk_i_movementtype; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementtype
    ADD CONSTRAINT pk_i_movementtype PRIMARY KEY (i_movementtype_id);


--
-- TOC entry 3323 (class 2606 OID 37976)
-- Name: i_product_warehouselocation pk_i_product_warehouselocation; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_product_warehouselocation
    ADD CONSTRAINT pk_i_product_warehouselocation PRIMARY KEY (i_product_warehouselocation_id);


--
-- TOC entry 3325 (class 2606 OID 37978)
-- Name: i_stock pk_i_stock; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_stock
    ADD CONSTRAINT pk_i_stock PRIMARY KEY (i_stock_id);


--
-- TOC entry 3327 (class 2606 OID 37980)
-- Name: i_stockcontrol pk_i_stockcontrol; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_stockcontrol
    ADD CONSTRAINT pk_i_stockcontrol PRIMARY KEY (i_stockcontrol_id);


--
-- TOC entry 3329 (class 2606 OID 37982)
-- Name: i_warehouse pk_i_warehouse; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_warehouse
    ADD CONSTRAINT pk_i_warehouse PRIMARY KEY (i_warehouse_id);


--
-- TOC entry 3331 (class 2606 OID 37984)
-- Name: i_warehouselocation pk_i_warehouselocation; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_warehouselocation
    ADD CONSTRAINT pk_i_warehouselocation PRIMARY KEY (i_warehouselocation_id);


--
-- TOC entry 3333 (class 2606 OID 37986)
-- Name: l_account pk_l_account; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_account
    ADD CONSTRAINT pk_l_account PRIMARY KEY (l_account_id);


--
-- TOC entry 3335 (class 2606 OID 37988)
-- Name: l_accounttype pk_l_accounttype; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_accounttype
    ADD CONSTRAINT pk_l_accounttype PRIMARY KEY (l_accounttype_id);


--
-- TOC entry 3337 (class 2606 OID 37990)
-- Name: l_campaign pk_l_campaign; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_campaign
    ADD CONSTRAINT pk_l_campaign PRIMARY KEY (l_campaign_id);


--
-- TOC entry 3339 (class 2606 OID 37992)
-- Name: l_campaign_accounttype pk_l_campaign_accounttype; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_campaign_accounttype
    ADD CONSTRAINT pk_l_campaign_accounttype PRIMARY KEY (l_campaign_accounttype_id);


--
-- TOC entry 3341 (class 2606 OID 37994)
-- Name: l_card pk_l_card; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_card
    ADD CONSTRAINT pk_l_card PRIMARY KEY (l_card_id);


--
-- TOC entry 3343 (class 2606 OID 37996)
-- Name: l_group pk_l_group; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_group
    ADD CONSTRAINT pk_l_group PRIMARY KEY (l_group_id);


--
-- TOC entry 3345 (class 2606 OID 37998)
-- Name: l_movement pk_l_movement; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_movement
    ADD CONSTRAINT pk_l_movement PRIMARY KEY (l_movement_id);


--
-- TOC entry 3347 (class 2606 OID 38000)
-- Name: l_org pk_l_org; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_org
    ADD CONSTRAINT pk_l_org PRIMARY KEY (l_org_id);


--
-- TOC entry 3349 (class 2606 OID 38002)
-- Name: l_prize pk_l_prize; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_prize
    ADD CONSTRAINT pk_l_prize PRIMARY KEY (l_prize_id);


--
-- TOC entry 3351 (class 2606 OID 38004)
-- Name: l_productpoints pk_l_productpoints; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_productpoints
    ADD CONSTRAINT pk_l_productpoints PRIMARY KEY (l_productpoints_id);


--
-- TOC entry 3353 (class 2606 OID 38006)
-- Name: mig_cowmap pk_mig_cowmap; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mig_cowmap
    ADD CONSTRAINT pk_mig_cowmap PRIMARY KEY (ch_almacen);


--
-- TOC entry 3355 (class 2606 OID 38008)
-- Name: mig_export pk_mig_export; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mig_export
    ADD CONSTRAINT pk_mig_export PRIMARY KEY (mig_export_id);


--
-- TOC entry 3357 (class 2606 OID 38010)
-- Name: mig_process pk_mig_process_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mig_process
    ADD CONSTRAINT pk_mig_process_id PRIMARY KEY (mig_process_id);


--
-- TOC entry 3359 (class 2606 OID 38012)
-- Name: mig_remote pk_mig_remote_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mig_remote
    ADD CONSTRAINT pk_mig_remote_id PRIMARY KEY (mig_remote_id);


--
-- TOC entry 3214 (class 1259 OID 38013)
-- Name: c_bpartner_taxid_ix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX c_bpartner_taxid_ix ON public.c_bpartner USING btree (taxid);


--
-- TOC entry 3252 (class 1259 OID 38014)
-- Name: c_product_value_ix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX c_product_value_ix ON public.c_product USING btree (value);


--
-- TOC entry 3317 (class 1259 OID 38015)
-- Name: fki_f_fleetvehicle_i_movementheader_fk; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX fki_f_fleetvehicle_i_movementheader_fk ON public.i_movementheader USING btree (f_fleetvehicle_id);


--
-- TOC entry 3233 (class 1259 OID 38016)
-- Name: idx_c_invoiceheader_created; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_c_invoiceheader_created ON public.c_invoiceheader USING btree (c_org_id, issale, c_doctype_id, created, documentno);


--
-- TOC entry 3361 (class 2606 OID 38017)
-- Name: ad_role_access ad_entity_ad_role_access_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_role_access
    ADD CONSTRAINT ad_entity_ad_role_access_fk FOREIGN KEY (ad_entity_id) REFERENCES public.ad_entity(ad_entity_id);


--
-- TOC entry 3362 (class 2606 OID 38022)
-- Name: ad_role_access ad_role_ad_role_access_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_role_access
    ADD CONSTRAINT ad_role_ad_role_access_fk FOREIGN KEY (ad_role_id) REFERENCES public.ad_role(ad_role_id);


--
-- TOC entry 3363 (class 2606 OID 38027)
-- Name: ad_role_user ad_role_ad_role_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_role_user
    ADD CONSTRAINT ad_role_ad_role_user_fk FOREIGN KEY (ad_role_id) REFERENCES public.ad_role(ad_role_id);


--
-- TOC entry 3383 (class 2606 OID 38032)
-- Name: c_org_access ad_role_c_org_access_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_org_access
    ADD CONSTRAINT ad_role_c_org_access_fk FOREIGN KEY (ad_role_id) REFERENCES public.ad_role(ad_role_id);


--
-- TOC entry 3364 (class 2606 OID 38037)
-- Name: ad_role_user ad_user_ad_role_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_role_user
    ADD CONSTRAINT ad_user_ad_role_user_fk FOREIGN KEY (ad_user_id) REFERENCES public.ad_user(ad_user_id);


--
-- TOC entry 3376 (class 2606 OID 38042)
-- Name: c_invoiceheader c_bpartner_c_invoiceheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoiceheader
    ADD CONSTRAINT c_bpartner_c_invoiceheader_fk FOREIGN KEY (c_bpartner_id) REFERENCES public.c_bpartner(c_bpartner_id);


--
-- TOC entry 3422 (class 2606 OID 38047)
-- Name: f_fleetgroup c_bpartner_f_fleetgroup_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetgroup
    ADD CONSTRAINT c_bpartner_f_fleetgroup_fk FOREIGN KEY (c_bpartner_id) REFERENCES public.c_bpartner(c_bpartner_id);


--
-- TOC entry 3440 (class 2606 OID 38052)
-- Name: h_worker c_bpartner_h_worker_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.h_worker
    ADD CONSTRAINT c_bpartner_h_worker_fk FOREIGN KEY (c_bpartner_id) REFERENCES public.c_bpartner(c_bpartner_id);


--
-- TOC entry 3446 (class 2606 OID 38057)
-- Name: i_movementheader c_bpartner_i_movementheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementheader
    ADD CONSTRAINT c_bpartner_i_movementheader_fk FOREIGN KEY (c_bpartner_id) REFERENCES public.c_bpartner(c_bpartner_id);


--
-- TOC entry 3461 (class 2606 OID 38062)
-- Name: l_account c_bpartner_l_account_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_account
    ADD CONSTRAINT c_bpartner_l_account_fk FOREIGN KEY (c_bpartner_id) REFERENCES public.c_bpartner(c_bpartner_id);


--
-- TOC entry 3360 (class 2606 OID 38067)
-- Name: ad_role c_client_ad_role_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_role
    ADD CONSTRAINT c_client_ad_role_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3365 (class 2606 OID 38072)
-- Name: ad_user c_client_ad_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ad_user
    ADD CONSTRAINT c_client_ad_user_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3366 (class 2606 OID 38077)
-- Name: c_bpartner c_client_c_bpartner_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_bpartner
    ADD CONSTRAINT c_client_c_bpartner_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3371 (class 2606 OID 38082)
-- Name: c_config c_client_c_config_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_config
    ADD CONSTRAINT c_client_c_config_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3382 (class 2606 OID 38087)
-- Name: c_org c_client_c_org_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_org
    ADD CONSTRAINT c_client_c_org_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3392 (class 2606 OID 38092)
-- Name: c_pricelistheader c_client_c_pricelist_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pricelistheader
    ADD CONSTRAINT c_client_c_pricelist_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3399 (class 2606 OID 38097)
-- Name: c_productgroup c_client_c_productgroup_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_productgroup
    ADD CONSTRAINT c_client_c_productgroup_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3402 (class 2606 OID 38102)
-- Name: c_producttype c_client_c_producttype_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_producttype
    ADD CONSTRAINT c_client_c_producttype_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3403 (class 2606 OID 38107)
-- Name: c_productuom c_client_c_productuom_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_productuom
    ADD CONSTRAINT c_client_c_productuom_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3408 (class 2606 OID 38112)
-- Name: c_promoheader c_client_c_promoheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_promoheader
    ADD CONSTRAINT c_client_c_promoheader_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3411 (class 2606 OID 38117)
-- Name: c_tax c_client_c_tax_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_tax
    ADD CONSTRAINT c_client_c_tax_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3412 (class 2606 OID 38122)
-- Name: c_taxgroup c_client_c_taxgroup_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_taxgroup
    ADD CONSTRAINT c_client_c_taxgroup_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3415 (class 2606 OID 38127)
-- Name: c_tendertype c_client_c_tendertype_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_tendertype
    ADD CONSTRAINT c_client_c_tendertype_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3416 (class 2606 OID 38132)
-- Name: f_fleet c_client_f_fleet_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleet
    ADD CONSTRAINT c_client_f_fleet_fk FOREIGN KEY (c_client_id) REFERENCES public.c_client(c_client_id);


--
-- TOC entry 3377 (class 2606 OID 38137)
-- Name: c_invoiceheader c_currency_c_invoiceheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoiceheader
    ADD CONSTRAINT c_currency_c_invoiceheader_fk FOREIGN KEY (c_currency_id) REFERENCES public.c_currency(c_currency_id);


--
-- TOC entry 3393 (class 2606 OID 38142)
-- Name: c_pricelistheader c_currency_c_pricelist_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pricelistheader
    ADD CONSTRAINT c_currency_c_pricelist_fk FOREIGN KEY (c_currency_id) REFERENCES public.c_currency(c_currency_id);


--
-- TOC entry 3367 (class 2606 OID 38147)
-- Name: c_cashdeposit c_currency_f_cashdeposit_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_cashdeposit
    ADD CONSTRAINT c_currency_f_cashdeposit_fk FOREIGN KEY (c_currency_id) REFERENCES public.c_currency(c_currency_id);


--
-- TOC entry 3471 (class 2606 OID 38152)
-- Name: l_prize c_currency_l_prize_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_prize
    ADD CONSTRAINT c_currency_l_prize_fk FOREIGN KEY (c_currency_id) REFERENCES public.c_currency(c_currency_id);


--
-- TOC entry 3385 (class 2606 OID 38157)
-- Name: c_periodcontrol c_daycontrol_c_periodcontrol_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_periodcontrol
    ADD CONSTRAINT c_daycontrol_c_periodcontrol_fk FOREIGN KEY (c_daycontrol_id) REFERENCES public.c_daycontrol(c_daycontrol_id);


--
-- TOC entry 3373 (class 2606 OID 38162)
-- Name: c_documentserial c_doctype_c_documentserial_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_documentserial
    ADD CONSTRAINT c_doctype_c_documentserial_fk FOREIGN KEY (c_doctype_id) REFERENCES public.c_doctype(c_doctype_id);


--
-- TOC entry 3447 (class 2606 OID 38167)
-- Name: i_movementheader c_doctype_i_movementheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementheader
    ADD CONSTRAINT c_doctype_i_movementheader_fk FOREIGN KEY (c_doctype_id) REFERENCES public.c_doctype(c_doctype_id);


--
-- TOC entry 3386 (class 2606 OID 38172)
-- Name: c_pos c_documentserial_c_pos_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pos
    ADD CONSTRAINT c_documentserial_c_pos_fk FOREIGN KEY (c_documentserial_id) REFERENCES public.c_documentserial(c_documentserial_id);


--
-- TOC entry 3378 (class 2606 OID 38177)
-- Name: c_invoiceheader c_invoiceheader_c_doctype_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoiceheader
    ADD CONSTRAINT c_invoiceheader_c_doctype_fk FOREIGN KEY (c_doctype_id) REFERENCES public.c_doctype(c_doctype_id);


--
-- TOC entry 3418 (class 2606 OID 38182)
-- Name: f_fleetdifference c_invoiceheader_f_fleetdifference_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetdifference
    ADD CONSTRAINT c_invoiceheader_f_fleetdifference_fk FOREIGN KEY (c_invoiceheader_id) REFERENCES public.c_invoiceheader(c_invoiceheader_id);


--
-- TOC entry 3448 (class 2606 OID 38187)
-- Name: i_movementheader c_invoiceheader_i_movementheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementheader
    ADD CONSTRAINT c_invoiceheader_i_movementheader_fk FOREIGN KEY (c_invoiceheader_id) REFERENCES public.c_invoiceheader(c_invoiceheader_id);


--
-- TOC entry 3372 (class 2606 OID 38192)
-- Name: c_daycontrol c_org_c_daycontrol_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_daycontrol
    ADD CONSTRAINT c_org_c_daycontrol_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3374 (class 2606 OID 38197)
-- Name: c_documentserial c_org_c_documentserial_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_documentserial
    ADD CONSTRAINT c_org_c_documentserial_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3379 (class 2606 OID 38202)
-- Name: c_invoiceheader c_org_c_invoiceheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoiceheader
    ADD CONSTRAINT c_org_c_invoiceheader_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3384 (class 2606 OID 38207)
-- Name: c_org_access c_org_c_org_access_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_org_access
    ADD CONSTRAINT c_org_c_org_access_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3387 (class 2606 OID 38212)
-- Name: c_pos c_org_c_pos_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pos
    ADD CONSTRAINT c_org_c_pos_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3404 (class 2606 OID 38217)
-- Name: c_promo_org c_org_c_promo_org_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_promo_org
    ADD CONSTRAINT c_org_c_promo_org_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3368 (class 2606 OID 38222)
-- Name: c_cashdeposit c_org_f_cashdeposit_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_cashdeposit
    ADD CONSTRAINT c_org_f_cashdeposit_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3419 (class 2606 OID 38227)
-- Name: f_fleetdifference c_org_f_fleetdifference_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetdifference
    ADD CONSTRAINT c_org_f_fleetdifference_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3435 (class 2606 OID 38232)
-- Name: f_pumpnetwork c_org_f_pumpnetwork_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pumpnetwork
    ADD CONSTRAINT c_org_f_pumpnetwork_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3459 (class 2606 OID 38237)
-- Name: i_warehouse c_org_i_warehouse_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_warehouse
    ADD CONSTRAINT c_org_i_warehouse_fk FOREIGN KEY (c_org_id) REFERENCES public.c_org(c_org_id);


--
-- TOC entry 3409 (class 2606 OID 38242)
-- Name: c_sale_shift c_periodcontrol_c_sale_shift_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_sale_shift
    ADD CONSTRAINT c_periodcontrol_c_sale_shift_fk FOREIGN KEY (c_periodcontrol_id) REFERENCES public.c_periodcontrol(c_periodcontrol_id);


--
-- TOC entry 3369 (class 2606 OID 38247)
-- Name: c_cashdeposit c_periodcontrol_f_cashdeposit_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_cashdeposit
    ADD CONSTRAINT c_periodcontrol_f_cashdeposit_fk FOREIGN KEY (c_periodcontrol_id) REFERENCES public.c_periodcontrol(c_periodcontrol_id);


--
-- TOC entry 3432 (class 2606 OID 38252)
-- Name: f_pump_worker c_periodcontrol_f_pump_worker_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump_worker
    ADD CONSTRAINT c_periodcontrol_f_pump_worker_fk FOREIGN KEY (c_periodcontrol_id) REFERENCES public.c_periodcontrol(c_periodcontrol_id);


--
-- TOC entry 3438 (class 2606 OID 38257)
-- Name: f_totalizer c_periodcontrol_f_totalizer_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_totalizer
    ADD CONSTRAINT c_periodcontrol_f_totalizer_fk FOREIGN KEY (c_periodcontrol_id) REFERENCES public.c_periodcontrol(c_periodcontrol_id);


--
-- TOC entry 3410 (class 2606 OID 38262)
-- Name: c_sale_shift c_pos_c_sale_shift_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_sale_shift
    ADD CONSTRAINT c_pos_c_sale_shift_fk FOREIGN KEY (c_pos_id) REFERENCES public.c_pos(c_pos_id);


--
-- TOC entry 3430 (class 2606 OID 38267)
-- Name: f_pump_pos c_pos_f_pump_pos_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump_pos
    ADD CONSTRAINT c_pos_f_pump_pos_fk FOREIGN KEY (c_pos_id) REFERENCES public.c_pos(c_pos_id);


--
-- TOC entry 3388 (class 2606 OID 38272)
-- Name: c_pos c_postype_c_pos_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pos
    ADD CONSTRAINT c_postype_c_pos_fk FOREIGN KEY (c_postype_id) REFERENCES public.c_postype(c_postype_id);


--
-- TOC entry 3390 (class 2606 OID 38277)
-- Name: c_pricelistdetail c_pricelistheader_c_pricelistdetail_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pricelistdetail
    ADD CONSTRAINT c_pricelistheader_c_pricelistdetail_fk FOREIGN KEY (c_pricelistheader_id) REFERENCES public.c_pricelistheader(c_pricelistheader_id);


--
-- TOC entry 3375 (class 2606 OID 38282)
-- Name: c_invoicedetail c_product_c_invoicedetail_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoicedetail
    ADD CONSTRAINT c_product_c_invoicedetail_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3391 (class 2606 OID 38287)
-- Name: c_pricelistdetail c_product_c_pricelistdetail_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pricelistdetail
    ADD CONSTRAINT c_product_c_pricelistdetail_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3400 (class 2606 OID 38292)
-- Name: c_productlink c_product_c_productlink_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_productlink
    ADD CONSTRAINT c_product_c_productlink_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3401 (class 2606 OID 38297)
-- Name: c_productlink c_product_c_productlink_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_productlink
    ADD CONSTRAINT c_product_c_productlink_fk1 FOREIGN KEY (linked_c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3406 (class 2606 OID 38302)
-- Name: c_promodetail c_product_c_promodetail_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_promodetail
    ADD CONSTRAINT c_product_c_promodetail_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3441 (class 2606 OID 38307)
-- Name: i_inventorydetail c_product_i_inventorydetail_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_inventorydetail
    ADD CONSTRAINT c_product_i_inventorydetail_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3444 (class 2606 OID 38312)
-- Name: i_movementdetail c_product_i_movementdetail_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementdetail
    ADD CONSTRAINT c_product_i_movementdetail_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3453 (class 2606 OID 38317)
-- Name: i_product_warehouselocation c_product_i_product_warehouselocation_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_product_warehouselocation
    ADD CONSTRAINT c_product_i_product_warehouselocation_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3455 (class 2606 OID 38322)
-- Name: i_stock c_product_i_stock_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_stock
    ADD CONSTRAINT c_product_i_stock_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3457 (class 2606 OID 38327)
-- Name: i_stockcontrol c_product_i_stockcontrol_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_stockcontrol
    ADD CONSTRAINT c_product_i_stockcontrol_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3472 (class 2606 OID 38332)
-- Name: l_prize c_product_l_prize_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_prize
    ADD CONSTRAINT c_product_l_prize_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3474 (class 2606 OID 38337)
-- Name: l_productpoints c_product_l_productpoints_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_productpoints
    ADD CONSTRAINT c_product_l_productpoints_fk FOREIGN KEY (c_product_id) REFERENCES public.c_product(c_product_id);


--
-- TOC entry 3394 (class 2606 OID 38342)
-- Name: c_product c_productfamily_c_product_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_product
    ADD CONSTRAINT c_productfamily_c_product_fk FOREIGN KEY (c_productfamily_id) REFERENCES public.c_productfamily(c_productfamily_id);


--
-- TOC entry 3395 (class 2606 OID 38347)
-- Name: c_product c_productgroup_c_product_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_product
    ADD CONSTRAINT c_productgroup_c_product_fk FOREIGN KEY (c_productgroup_id) REFERENCES public.c_productgroup(c_productgroup_id);


--
-- TOC entry 3407 (class 2606 OID 38352)
-- Name: c_promodetail c_productgroup_c_promodetail_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_promodetail
    ADD CONSTRAINT c_productgroup_c_promodetail_fk FOREIGN KEY (c_productgroup_id) REFERENCES public.c_productgroup(c_productgroup_id);


--
-- TOC entry 3398 (class 2606 OID 38357)
-- Name: c_productfamily c_producttype_c_productfamily_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_productfamily
    ADD CONSTRAINT c_producttype_c_productfamily_fk FOREIGN KEY (c_producttype_id) REFERENCES public.c_producttype(c_producttype_id);


--
-- TOC entry 3396 (class 2606 OID 38362)
-- Name: c_product c_productuom_c_product_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_product
    ADD CONSTRAINT c_productuom_c_product_fk FOREIGN KEY (c_productuom_id) REFERENCES public.c_productuom(c_productuom_id);


--
-- TOC entry 3405 (class 2606 OID 38367)
-- Name: c_promo_org c_promoheader_c_promo_org_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_promo_org
    ADD CONSTRAINT c_promoheader_c_promo_org_fk FOREIGN KEY (c_promoheader_id) REFERENCES public.c_promoheader(c_promoheader_id);


--
-- TOC entry 3381 (class 2606 OID 38372)
-- Name: c_invoicetax c_tax_c_invoicetax_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoicetax
    ADD CONSTRAINT c_tax_c_invoicetax_fk FOREIGN KEY (c_tax_id) REFERENCES public.c_tax(c_tax_id);


--
-- TOC entry 3413 (class 2606 OID 38377)
-- Name: c_taxgroup_tax c_tax_c_taxgroup_tax_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_taxgroup_tax
    ADD CONSTRAINT c_tax_c_taxgroup_tax_fk FOREIGN KEY (c_tax_id) REFERENCES public.c_tax(c_tax_id);


--
-- TOC entry 3397 (class 2606 OID 38382)
-- Name: c_product c_taxgroup_c_product_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_product
    ADD CONSTRAINT c_taxgroup_c_product_fk FOREIGN KEY (c_taxgroup_id) REFERENCES public.c_taxgroup(c_taxgroup_id);


--
-- TOC entry 3414 (class 2606 OID 38387)
-- Name: c_taxgroup_tax c_taxgroup_c_taxgroup_tax_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_taxgroup_tax
    ADD CONSTRAINT c_taxgroup_c_taxgroup_tax_fk FOREIGN KEY (c_taxgroup_id) REFERENCES public.c_taxgroup(c_taxgroup_id);


--
-- TOC entry 3380 (class 2606 OID 38392)
-- Name: c_invoiceheader c_tendertype_c_invoiceheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_invoiceheader
    ADD CONSTRAINT c_tendertype_c_invoiceheader_fk FOREIGN KEY (c_tendertype_id) REFERENCES public.c_tendertype(c_tendertype_id);


--
-- TOC entry 3436 (class 2606 OID 38397)
-- Name: f_pumpnetwork f_driver_f_pumpnetwork_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pumpnetwork
    ADD CONSTRAINT f_driver_f_pumpnetwork_fk FOREIGN KEY (f_driver_id) REFERENCES public.f_driver(f_driver_id);


--
-- TOC entry 3423 (class 2606 OID 38402)
-- Name: f_fleetgroup f_fleet_f_fleetgroup_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetgroup
    ADD CONSTRAINT f_fleet_f_fleetgroup_fk FOREIGN KEY (f_fleet_id) REFERENCES public.f_fleet(f_fleet_id);


--
-- TOC entry 3428 (class 2606 OID 38407)
-- Name: f_pump f_fleet_f_pump_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump
    ADD CONSTRAINT f_fleet_f_pump_fk FOREIGN KEY (f_fleet_id) REFERENCES public.f_fleet(f_fleet_id);


--
-- TOC entry 3425 (class 2606 OID 38412)
-- Name: f_fleetvehicle f_fleetgroup_f_fleetvehicle_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetvehicle
    ADD CONSTRAINT f_fleetgroup_f_fleetvehicle_fk FOREIGN KEY (f_fleetgroup_id) REFERENCES public.f_fleetgroup(f_fleetgroup_id);


--
-- TOC entry 3426 (class 2606 OID 38417)
-- Name: f_fleetvehicle f_fleetlockoutreason_f_fleetvehicle_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetvehicle
    ADD CONSTRAINT f_fleetlockoutreason_f_fleetvehicle_fk FOREIGN KEY (f_fleetlockoutreason_id) REFERENCES public.f_fleetlockoutreason(f_fleetlockoutreason_id);


--
-- TOC entry 3417 (class 2606 OID 38422)
-- Name: f_fleet f_fleetprovider_f_fleet_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleet
    ADD CONSTRAINT f_fleetprovider_f_fleet_fk FOREIGN KEY (f_fleetprovider_id) REFERENCES public.f_fleetprovider(f_fleetprovider_id);


--
-- TOC entry 3424 (class 2606 OID 38427)
-- Name: f_fleetlockoutreason f_fleetprovider_f_fleetlockoutreason_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetlockoutreason
    ADD CONSTRAINT f_fleetprovider_f_fleetlockoutreason_fk FOREIGN KEY (f_fleetprovider_id) REFERENCES public.f_fleetprovider(f_fleetprovider_id);


--
-- TOC entry 3420 (class 2606 OID 38432)
-- Name: f_fleetdifference f_fleetvehicle_f_fleetdifference_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetdifference
    ADD CONSTRAINT f_fleetvehicle_f_fleetdifference_fk FOREIGN KEY (f_fleetvehicle_id) REFERENCES public.f_fleetvehicle(f_fleetvehicle_id);


--
-- TOC entry 3449 (class 2606 OID 38437)
-- Name: i_movementheader f_fleetvehicle_i_movementheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementheader
    ADD CONSTRAINT f_fleetvehicle_i_movementheader_fk FOREIGN KEY (f_fleetvehicle_id) REFERENCES public.f_fleetvehicle(f_fleetvehicle_id);


--
-- TOC entry 3437 (class 2606 OID 38442)
-- Name: f_sale f_grade_f_sale_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_sale
    ADD CONSTRAINT f_grade_f_sale_fk FOREIGN KEY (f_grade_id) REFERENCES public.f_grade(f_grade_id);


--
-- TOC entry 3439 (class 2606 OID 38447)
-- Name: f_totalizer f_grade_f_totalizer_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_totalizer
    ADD CONSTRAINT f_grade_f_totalizer_fk FOREIGN KEY (f_grade_id) REFERENCES public.f_grade(f_grade_id);


--
-- TOC entry 3427 (class 2606 OID 38452)
-- Name: f_grade f_pump_f_grade_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_grade
    ADD CONSTRAINT f_pump_f_grade_fk FOREIGN KEY (f_pump_id) REFERENCES public.f_pump(f_pump_id);


--
-- TOC entry 3431 (class 2606 OID 38457)
-- Name: f_pump_pos f_pump_f_pump_pos_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump_pos
    ADD CONSTRAINT f_pump_f_pump_pos_fk FOREIGN KEY (f_pump_id) REFERENCES public.f_pump(f_pump_id);


--
-- TOC entry 3433 (class 2606 OID 38462)
-- Name: f_pump_worker f_pump_f_pump_worker_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump_worker
    ADD CONSTRAINT f_pump_f_pump_worker_fk FOREIGN KEY (f_pump_id) REFERENCES public.f_pump(f_pump_id);


--
-- TOC entry 3450 (class 2606 OID 38467)
-- Name: i_movementheader f_pump_i_movementheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementheader
    ADD CONSTRAINT f_pump_i_movementheader_fk FOREIGN KEY (f_pump_id) REFERENCES public.f_pump(f_pump_id);


--
-- TOC entry 3429 (class 2606 OID 38472)
-- Name: f_pump f_pumpnetwork_f_pump_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump
    ADD CONSTRAINT f_pumpnetwork_f_pump_fk FOREIGN KEY (f_pumpnetwork_id) REFERENCES public.f_pumpnetwork(f_pumpnetwork_id);


--
-- TOC entry 3476 (class 2606 OID 38477)
-- Name: mig_export fk_mig_export_mig_remote; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mig_export
    ADD CONSTRAINT fk_mig_export_mig_remote FOREIGN KEY (mig_remote_id) REFERENCES public.mig_remote(mig_remote_id);


--
-- TOC entry 3477 (class 2606 OID 38482)
-- Name: mig_process fk_mig_process_mig_remote_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mig_process
    ADD CONSTRAINT fk_mig_process_mig_remote_id FOREIGN KEY (mig_remote_id) REFERENCES public.mig_remote(mig_remote_id);


--
-- TOC entry 3370 (class 2606 OID 38487)
-- Name: c_cashdeposit h_worker_c_cashdeposit_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_cashdeposit
    ADD CONSTRAINT h_worker_c_cashdeposit_fk FOREIGN KEY (h_worker_id) REFERENCES public.h_worker(h_worker_id);


--
-- TOC entry 3434 (class 2606 OID 38492)
-- Name: f_pump_worker h_worker_f_pump_worker_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_pump_worker
    ADD CONSTRAINT h_worker_f_pump_worker_fk FOREIGN KEY (h_worker_id) REFERENCES public.h_worker(h_worker_id);


--
-- TOC entry 3442 (class 2606 OID 38497)
-- Name: i_inventorydetail i_inventoryheader_i_inventorydetail_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_inventorydetail
    ADD CONSTRAINT i_inventoryheader_i_inventorydetail_fk FOREIGN KEY (i_inventoryheader_id) REFERENCES public.i_inventoryheader(i_inventoryheader_id);


--
-- TOC entry 3421 (class 2606 OID 38502)
-- Name: f_fleetdifference i_movementheader_f_fleetdifference_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.f_fleetdifference
    ADD CONSTRAINT i_movementheader_f_fleetdifference_fk FOREIGN KEY (i_movementheader_id) REFERENCES public.i_movementheader(i_movementheader_id);


--
-- TOC entry 3445 (class 2606 OID 38507)
-- Name: i_movementdetail i_movementheader_i_movementdetail_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementdetail
    ADD CONSTRAINT i_movementheader_i_movementdetail_fk FOREIGN KEY (i_movementheader_id) REFERENCES public.i_movementheader(i_movementheader_id);


--
-- TOC entry 3389 (class 2606 OID 38512)
-- Name: c_pos i_warehouse_c_pos_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_pos
    ADD CONSTRAINT i_warehouse_c_pos_fk FOREIGN KEY (i_warehouse_id) REFERENCES public.i_warehouse(i_warehouse_id);


--
-- TOC entry 3451 (class 2606 OID 38517)
-- Name: i_movementheader i_warehouse_i_movementheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementheader
    ADD CONSTRAINT i_warehouse_i_movementheader_fk FOREIGN KEY (source_i_warehouse_id) REFERENCES public.i_warehouse(i_warehouse_id);


--
-- TOC entry 3452 (class 2606 OID 38522)
-- Name: i_movementheader i_warehouse_i_movementheader_fk1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_movementheader
    ADD CONSTRAINT i_warehouse_i_movementheader_fk1 FOREIGN KEY (destination_i_warehouse_id) REFERENCES public.i_warehouse(i_warehouse_id);


--
-- TOC entry 3456 (class 2606 OID 38527)
-- Name: i_stock i_warehouse_i_stock_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_stock
    ADD CONSTRAINT i_warehouse_i_stock_fk FOREIGN KEY (i_warehouse_id) REFERENCES public.i_warehouse(i_warehouse_id);


--
-- TOC entry 3458 (class 2606 OID 38532)
-- Name: i_stockcontrol i_warehouse_i_stockcontrol_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_stockcontrol
    ADD CONSTRAINT i_warehouse_i_stockcontrol_fk FOREIGN KEY (i_warehouse_id) REFERENCES public.i_warehouse(i_warehouse_id);


--
-- TOC entry 3460 (class 2606 OID 38537)
-- Name: i_warehouselocation i_warehouse_i_warehouselocation_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_warehouselocation
    ADD CONSTRAINT i_warehouse_i_warehouselocation_fk FOREIGN KEY (i_warehouse_id) REFERENCES public.i_warehouse(i_warehouse_id);


--
-- TOC entry 3443 (class 2606 OID 38542)
-- Name: i_inventoryheader i_warehouselocation_i_inventoryheader_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_inventoryheader
    ADD CONSTRAINT i_warehouselocation_i_inventoryheader_fk FOREIGN KEY (i_warehouselocation_id) REFERENCES public.i_warehouselocation(i_warehouselocation_id);


--
-- TOC entry 3454 (class 2606 OID 38547)
-- Name: i_product_warehouselocation i_warehouselocation_i_product_warehouselocation_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.i_product_warehouselocation
    ADD CONSTRAINT i_warehouselocation_i_product_warehouselocation_fk FOREIGN KEY (i_warehouselocation_id) REFERENCES public.i_warehouselocation(i_warehouselocation_id);


--
-- TOC entry 3467 (class 2606 OID 38552)
-- Name: l_card l_account_l_card_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_card
    ADD CONSTRAINT l_account_l_card_fk FOREIGN KEY (l_account_id) REFERENCES public.l_account(l_account_id);


--
-- TOC entry 3462 (class 2606 OID 38557)
-- Name: l_account l_accounttype_l_account_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_account
    ADD CONSTRAINT l_accounttype_l_account_fk FOREIGN KEY (l_accounttype_id) REFERENCES public.l_accounttype(l_accounttype_id);


--
-- TOC entry 3465 (class 2606 OID 38562)
-- Name: l_campaign_accounttype l_accounttype_l_campaign_accounttype_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_campaign_accounttype
    ADD CONSTRAINT l_accounttype_l_campaign_accounttype_fk FOREIGN KEY (l_accounttype_id) REFERENCES public.l_accounttype(l_accounttype_id);


--
-- TOC entry 3466 (class 2606 OID 38567)
-- Name: l_campaign_accounttype l_campaign_l_campaign_accounttype_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_campaign_accounttype
    ADD CONSTRAINT l_campaign_l_campaign_accounttype_fk FOREIGN KEY (l_campaign_id) REFERENCES public.l_campaign(l_campaign_id);


--
-- TOC entry 3473 (class 2606 OID 38572)
-- Name: l_prize l_campaign_l_prize_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_prize
    ADD CONSTRAINT l_campaign_l_prize_fk FOREIGN KEY (l_campaign_id) REFERENCES public.l_campaign(l_campaign_id);


--
-- TOC entry 3475 (class 2606 OID 38577)
-- Name: l_productpoints l_campaign_l_productpoints_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_productpoints
    ADD CONSTRAINT l_campaign_l_productpoints_fk FOREIGN KEY (l_campaign_id) REFERENCES public.l_campaign(l_campaign_id);


--
-- TOC entry 3468 (class 2606 OID 38582)
-- Name: l_movement l_card_l_movement_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_movement
    ADD CONSTRAINT l_card_l_movement_fk FOREIGN KEY (l_card_id) REFERENCES public.l_card(l_card_id);


--
-- TOC entry 3463 (class 2606 OID 38587)
-- Name: l_accounttype l_group_l_accounttype_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_accounttype
    ADD CONSTRAINT l_group_l_accounttype_fk FOREIGN KEY (l_group_id) REFERENCES public.l_group(l_group_id);


--
-- TOC entry 3464 (class 2606 OID 38592)
-- Name: l_campaign l_group_l_campaign_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_campaign
    ADD CONSTRAINT l_group_l_campaign_fk FOREIGN KEY (l_group_id) REFERENCES public.l_group(l_group_id);


--
-- TOC entry 3470 (class 2606 OID 38597)
-- Name: l_org l_group_l_org_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_org
    ADD CONSTRAINT l_group_l_org_fk FOREIGN KEY (l_group_id) REFERENCES public.l_group(l_group_id);


--
-- TOC entry 3469 (class 2606 OID 38602)
-- Name: l_movement l_org_l_movement_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.l_movement
    ADD CONSTRAINT l_org_l_movement_fk FOREIGN KEY (l_org_id) REFERENCES public.l_org(l_org_id);


-- Completed on 2021-02-10 08:53:44

--
-- PostgreSQL database dump complete
--

